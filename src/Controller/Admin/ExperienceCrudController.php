<?php

namespace App\Controller\Admin;

use App\Entity\Experience;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class ExperienceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Experience::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('doświadczenie')
            ->setEntityLabelInPlural('Doświadczenie')
            ->setPageTitle(Crud::PAGE_INDEX, 'Doświadczenie')
            ->setPageTitle(Crud::PAGE_NEW, 'Nowe doświadczenie')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edytuj doświadczenie')
            ->setSearchFields(['company', 'role', 'description'])
            ->setDefaultSort(['sortOrder' => 'ASC', 'startDate' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setAutofocusSearch();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('published', 'Opublikowane'))
            ->add(BooleanFilter::new('current', 'Obecne'))
            ->add(TextFilter::new('company', 'Firma'))
            ->add(TextFilter::new('role', 'Stanowisko'))
            ->add(DateTimeFilter::new('startDate', 'Data rozpoczęcia'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addColumn(8);
        yield TextField::new('company', 'Firma')->setRequired(true)->setMaxLength(180);
        yield TextField::new('role', 'Stanowisko')->setRequired(true)->setMaxLength(180);
        yield TextareaField::new('description', 'Opis')
            ->setRequired(true)
            ->setHelp('Krótkie podsumowanie roli.')
            ->hideOnIndex();
        yield ArrayField::new('bullets', 'Osiągnięcia')
            ->setRequired(false)
            ->setHelp('Opcjonalnie. Lista punktów na stronie publicznej tylko gdy jest choć jeden wpis.')
            ->hideOnIndex();
        yield AssociationField::new('stacks', 'Technologie')
            ->setRequired(false)
            ->autocomplete()
            ->setFormTypeOption('by_reference', false)
            ->setHelp('Wybierz istniejące technologie. Nowe dodaj najpierw w module Stack.')
            ->formatValue(static function ($value, Experience $entity): string {
                return implode(', ', $entity->getStacks()->map(static fn ($s) => $s->getName())->toArray());
            });

        yield FormField::addColumn(4);
        yield DateField::new('startDate', 'Data rozpoczęcia')->setRequired(true);
        yield DateField::new('endDate', 'Data zakończenia')
            ->setRequired(false)
            ->setHelp('Zostaw puste, jeśli to obecne stanowisko.');
        yield BooleanField::new('current', 'Obecne')
            ->renderAsSwitch(true)
            ->setHelp('Oznacz jako obecne stanowisko.');
        yield BooleanField::new('published', 'Opublikowane')
            ->renderAsSwitch(true)
            ->setHelp('Na stronie publicznej widać tylko opublikowane wpisy.');
        yield IntegerField::new('sortOrder', 'Kolejność')
            ->setHelp('Niższe liczby pojawiają się wcześniej.')
            ->setFormTypeOption('attr', ['min' => 0]);
        yield TextField::new('period')
            ->onlyOnIndex()
            ->setLabel('Okres');
    }
}
