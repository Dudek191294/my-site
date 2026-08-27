<?php

namespace App\Controller\Admin;

use App\Entity\Stack;
use App\Entity\StackCategory;
use App\Service\Icon\StackIconCatalog;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class StackCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly StackIconCatalog $iconCatalog,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Stack::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Stack')
            ->setEntityLabelInPlural('Stack')
            ->setPageTitle(Crud::PAGE_INDEX, 'Stack')
            ->setPageTitle(Crud::PAGE_NEW, 'New technology')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit technology')
            ->setSearchFields(['name', 'slug', 'icon'])
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
            ->add(BooleanFilter::new('published'))
            ->add(BooleanFilter::new('featured'))
            ->add(ChoiceFilter::new('category')->setChoices($categoryChoices))
            ->add(TextFilter::new('name'));
    }

    public function configureFields(string $pageName): iterable
    {
        $iconChoices = $this->iconCatalog->choices();

        yield FormField::addColumn(8);
        yield TextField::new('name')
            ->setRequired(true)
            ->setMaxLength(120);
        yield SlugField::new('slug')
            ->setTargetFieldName('name')
            ->setRequired(true)
            ->setHelp('Unique identifier, e.g. react, postgresql, tailwindcss.');
        yield ChoiceField::new('category')
            ->setRequired(true)
            ->setChoices($this->categoryChoices())
            ->renderAsNativeWidget();

        if ($iconChoices === []) {
            yield TextField::new('icon')
                ->setRequired(false)
                ->setHelp('No local icons yet. Run: php bin/console app:import-simple-icons react symfony php postgresql docker tailwindcss')
                ->setFormTypeOption('attr', [
                    'placeholder' => 'react',
                    'pattern' => '[a-z0-9]{1,80}',
                    'maxlength' => 80,
                ]);
        } else {
            yield ChoiceField::new('icon')
                ->setRequired(false)
                ->setChoices($iconChoices)
                ->renderAsNativeWidget()
                ->setHelp('Local SVG from public/icons/stack/. Import more with app:import-simple-icons.');
        }

        yield ColorField::new('color')
            ->setRequired(false)
            ->setHelp('Optional brand color. Public site uses monochrome icons by default.')
            ->hideOnIndex();
        yield UrlField::new('websiteUrl', 'Website URL')
            ->setRequired(false)
            ->hideOnIndex();

        yield FormField::addColumn(4);
        yield BooleanField::new('published')->renderAsSwitch(true);
        yield BooleanField::new('featured')->renderAsSwitch(true);
        yield IntegerField::new('sortOrder', 'Sort order')
            ->setHelp('Order within the category (lower first).')
            ->setFormTypeOption('attr', ['min' => 0]);
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
}
