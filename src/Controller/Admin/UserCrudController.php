<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('użytkownika')
            ->setEntityLabelInPlural('Użytkownicy')
            ->setPageTitle(Crud::PAGE_INDEX, 'Użytkownicy')
            ->setPageTitle(Crud::PAGE_NEW, 'Nowy użytkownik')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edytuj użytkownika')
            ->setSearchFields(['email'])
            ->setDefaultSort(['id' => 'ASC'])
            ->setPaginatorPageSize(20);
    }

    public function configureActions(Actions $actions): Actions
    {
        $canDelete = fn (User $user): bool => $this->canDeleteUser($user);

        return $actions
            ->disable(Action::BATCH_DELETE)
            ->update(Crud::PAGE_INDEX, Action::DELETE, static function (Action $action) use ($canDelete) {
                return $action->displayIf(static function ($entity) use ($canDelete) {
                    return $entity instanceof User && $canDelete($entity);
                });
            });
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield FormField::addColumn(8);
        yield EmailField::new('email', 'E-mail')
            ->setRequired(true)
            ->setMaxLength(180);
        yield TextField::new('plainPassword', 'Hasło')
            ->setFormType(PasswordType::class)
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->onlyOnForms()
            ->setHelp($pageName === Crud::PAGE_EDIT
                ? 'Zostaw puste, aby nie zmieniać hasła. Minimum 8 znaków.'
                : 'Minimum 8 znaków.');

        yield FormField::addColumn(4);
        yield ChoiceField::new('formRoles', 'Role')
            ->setChoices(['Administrator' => 'ROLE_ADMIN'])
            ->allowMultipleChoices()
            ->renderExpanded()
            ->setRequired(true)
            ->setHelp('Administrator ma dostęp do panelu.')
            ->hideOnIndex();
    }

    public function createEntity(string $entityFqcn): User
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        return $user;
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            parent::persistEntity($entityManager, $entityInstance);

            return;
        }

        $plainPassword = $entityInstance->getPlainPassword();
        if ($plainPassword === null || $plainPassword === '') {
            throw new \InvalidArgumentException('Hasło jest wymagane przy tworzeniu użytkownika.');
        }

        $this->hashPassword($entityInstance, $plainPassword);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            parent::updateEntity($entityManager, $entityInstance);

            return;
        }

        $plainPassword = $entityInstance->getPlainPassword();
        if (\is_string($plainPassword) && $plainPassword !== '') {
            $this->hashPassword($entityInstance, $plainPassword);
        } else {
            $entityInstance->erasePlainPassword();
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof User && !$this->canDeleteUser($entityInstance)) {
            throw new \LogicException('Nie można usunąć własnego konta ani ostatniego administratora.');
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }

    private function hashPassword(User $user, string $plainPassword): void
    {
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $user->erasePlainPassword();
    }

    private function canDeleteUser(User $user): bool
    {
        $current = $this->getUser();
        if ($current instanceof User && $current->getId() === $user->getId()) {
            return false;
        }

        if ($user->isAdmin() && $this->userRepository->countAdmins() <= 1) {
            return false;
        }

        return true;
    }
}
