<?php

namespace App\Controller\Admin;

use App\Entity\SocialLink;
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
class SocialLinkCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SocialLink::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('link społecznościowy')
            ->setEntityLabelInPlural('Linki społecznościowe')
            ->setPageTitle(Crud::PAGE_INDEX, 'Linki społecznościowe')
            ->setPageTitle(Crud::PAGE_NEW, 'Nowy link społecznościowy')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edytuj link społecznościowy')
            ->setSearchFields(['label', 'platform', 'url'])
            ->setDefaultSort(['sortOrder' => 'ASC'])
            ->setPaginatorPageSize(20)
            ->setAutofocusSearch();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('published', 'Opublikowany'))
            ->add(ChoiceFilter::new('platform', 'Platforma')->setChoices($this->platformChoices()))
            ->add(TextFilter::new('label', 'Etykieta'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addColumn(8);
        yield ChoiceField::new('platform', 'Platforma')
            ->setRequired(true)
            ->setChoices($this->platformChoices())
            ->renderAsNativeWidget()
            ->setHelp('Jeden link na platformę (unikalny).');
        yield TextField::new('label', 'Etykieta')
            ->setRequired(true)
            ->setMaxLength(80)
            ->setFormTypeOption('attr', ['placeholder' => 'GitHub']);
        yield TextField::new('url', 'URL')
            ->setRequired(true)
            ->setHelp('Pełny URL albo adres mailto:.')
            ->setFormTypeOption('attr', ['placeholder' => 'https://github.com/you']);
        yield TextField::new('icon', 'Ikona')
            ->setRequired(false)
            ->setHelp('Identyfikator/slug ikony, nie SVG.')
            ->hideOnIndex();

        yield FormField::addColumn(4);
        yield BooleanField::new('published', 'Opublikowany')->renderAsSwitch(true);
        yield IntegerField::new('sortOrder', 'Kolejność')
            ->setHelp('Niższe liczby pojawiają się wcześniej.')
            ->setFormTypeOption('attr', ['min' => 0]);
    }

    /**
     * @return array<string, string>
     */
    private function platformChoices(): array
    {
        return [
            'GitHub' => 'github',
            'LinkedIn' => 'linkedin',
            'E-mail' => 'email',
            'X / Twitter' => 'x',
            'Inne' => 'other',
        ];
    }
}
