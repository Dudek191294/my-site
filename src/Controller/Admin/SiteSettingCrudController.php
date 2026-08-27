<?php

namespace App\Controller\Admin;

use App\Entity\SiteSetting;
use App\Repository\SiteSettingRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class SiteSettingCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly SiteSettingRepository $siteSettingRepository,
        private readonly AdminUrlGeneratorInterface $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return SiteSetting::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('ustawienia strony')
            ->setEntityLabelInPlural('Ustawienia strony')
            ->setPageTitle(Crud::PAGE_INDEX, 'Ustawienia strony')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edytuj ustawienia strony')
            ->setPageTitle(Crud::PAGE_NEW, 'Utwórz ustawienia strony')
            ->setSearchFields(['siteName', 'headline', 'contactEmail'])
            ->setDefaultSort(['id' => 'ASC'])
            ->setPaginatorPageSize(10);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        if ($this->siteSettingRepository->count([]) > 0) {
            $actions->disable(Action::NEW);
        }

        return $actions->disable(Action::BATCH_DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab('Marka i hero');
        yield TextField::new('siteName', 'Nazwa strony')
            ->setRequired(true)
            ->setHelp('Używana w nawigacji, stopce i tytułach stron.');
        yield TextField::new('roleTitle', 'Tytuł roli')
            ->setRequired(true)
            ->setHelp('Nadtytuł w hero, np. Full-Stack Developer.');
        yield TextField::new('headline', 'Nagłówek')
            ->setRequired(true);
        yield TextareaField::new('positioning', 'Pozycjonowanie')
            ->setRequired(true)
            ->setHelp('Jedno lub dwa zdania pod nagłówkiem.')
            ->hideOnIndex();

        yield FormField::addTab('O mnie');
        yield TextareaField::new('aboutBody', 'Treść O mnie')
            ->setRequired(true)
            ->setHelp('Akapity oddzielaj pustą linią.')
            ->hideOnIndex();
        yield TextField::new('location', 'Lokalizacja')->setRequired(false);
        yield TextField::new('availability', 'Dostępność')->setRequired(false);
        yield TextField::new('workMode', 'Tryb pracy')
            ->setRequired(false)
            ->setHelp('np. Zdalnie / Hybryda')
            ->setFormTypeOption('attr', ['placeholder' => 'Zdalnie / Hybryda']);
        yield ArrayField::new('principles', 'Zasady')
            ->setHelp('Zasady pracy pokazywane w sekcji O mnie.')
            ->hideOnIndex();

        yield FormField::addTab('Wstępy sekcji');
        yield TextareaField::new('projectsIntro', 'Wstęp realizacji')
            ->setRequired(false)
            ->setHelp('Opcjonalny akapit pod nagłówkiem „Wybrane realizacje”.')
            ->hideOnIndex();
        yield TextareaField::new('experienceIntro', 'Wstęp doświadczenia')
            ->setRequired(false)
            ->hideOnIndex();
        yield TextareaField::new('stackIntro', 'Wstęp stacku')
            ->setRequired(false)
            ->hideOnIndex();
        yield TextareaField::new('githubIntro', 'GitHub / open source')
            ->setRequired(false)
            ->setHelp('Opcjonalny blok przy stacku. Ukrywany, gdy puste i nie ma linku GitHub.')
            ->hideOnIndex();

        yield FormField::addTab('Kontakt i SEO');
        yield TextareaField::new('contactIntro', 'Wstęp kontaktu')
            ->setRequired(false)
            ->hideOnIndex();
        yield EmailField::new('contactEmail', 'E-mail kontaktowy')
            ->setRequired(false);
        yield TextField::new('metaDescription', 'Opis meta')
            ->setRequired(false)
            ->setMaxLength(300)
            ->setHelp('Trafia do meta description w nagłówku HTML.')
            ->hideOnIndex();
        yield DateTimeField::new('updatedAt', 'Zaktualizowano')
            ->hideOnForm()
            ->setFormat('short', 'medium');
    }

    public function createEntity(string $entityFqcn): SiteSetting
    {
        if ($this->siteSettingRepository->count([]) > 0) {
            throw $this->createAccessDeniedException('Ustawienia strony już istnieją. Edytuj istniejący rekord.');
        }

        return new SiteSetting();
    }

    public function index(AdminContext $context): KeyValueStore|Response
    {
        $existing = $this->siteSettingRepository->findSingleton();
        if ($existing instanceof SiteSetting) {
            return $this->redirect(
                $this->adminUrlGenerator
                    ->setController(self::class)
                    ->setAction(Action::EDIT)
                    ->setEntityId($existing->getId())
                    ->generateUrl()
            );
        }

        return parent::index($context);
    }
}
