<?php

namespace App\Controller\Admin;

use App\Entity\Stack;
use App\Entity\StackCategory;
use App\Service\Icon\SimpleIconImporter;
use App\Service\Icon\StackIconCatalog;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class StackCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly StackIconCatalog $iconCatalog,
        private readonly SimpleIconImporter $iconImporter,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Stack::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('technologię')
            ->setEntityLabelInPlural('Stack')
            ->setPageTitle(Crud::PAGE_INDEX, 'Stack')
            ->setPageTitle(Crud::PAGE_NEW, 'Nowa technologia')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edytuj technologię')
            ->setSearchFields(['name', 'icon'])
            ->setDefaultSort(['category' => 'ASC', 'sortOrder' => 'ASC'])
            ->setPaginatorPageSize(30)
            ->setAutofocusSearch();
    }

    public function configureFilters(Filters $filters): Filters
    {
        $categoryChoices = [];
        foreach (StackCategory::cases() as $category) {
            $categoryChoices[$category->label()] = $category->value;
        }

        return $filters
            ->add(BooleanFilter::new('published', 'Opublikowana'))
            ->add(BooleanFilter::new('featured', 'Wyróżniona'))
            ->add(ChoiceFilter::new('category', 'Kategoria')->setChoices($categoryChoices))
            ->add(TextFilter::new('name', 'Nazwa'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addColumn(8);
        yield TextField::new('name', 'Nazwa')
            ->setRequired(true)
            ->setMaxLength(120);
        yield ChoiceField::new('category', 'Kategoria')
            ->setRequired(true)
            ->setChoices($this->categoryChoices())
            ->renderAsNativeWidget();
        yield ChoiceField::new('icon', 'Ikona')
            ->setRequired(false)
            ->setChoices($this->iconCatalog->choices())
            ->autocomplete()
            ->setHelp('Szukaj po nazwie (np. Node.js). Slugi znajdziesz na simpleicons.org. Ikona zostanie skopiowana lokalnie przy zapisie.');

        yield FormField::addColumn(4);
        yield BooleanField::new('published', 'Opublikowana')->renderAsSwitch(true);
        yield BooleanField::new('featured', 'Wyróżniona')->renderAsSwitch(true);
        yield IntegerField::new('sortOrder', 'Kolejność')
            ->setHelp('Kolejność w kategorii (niższe najpierw).')
            ->setFormTypeOption('attr', ['min' => 0]);
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Stack) {
            $this->ensureIconImported($entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Stack) {
            $this->ensureIconImported($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * @return array<string, StackCategory>
     */
    private function categoryChoices(): array
    {
        $choices = [];
        foreach (StackCategory::cases() as $category) {
            $choices[$category->label()] = $category;
        }

        return $choices;
    }

    private function ensureIconImported(Stack $stack): void
    {
        $icon = $stack->getIcon();
        if ($icon === null || $icon === '') {
            return;
        }

        $report = $this->iconImporter->import($icon);
        if ($report->hasErrors()) {
            throw new \InvalidArgumentException(sprintf(
                'Nie udało się zaimportować ikony "%s": %s',
                $icon,
                $report->firstError() ?? 'nieznany błąd',
            ));
        }
    }
}
