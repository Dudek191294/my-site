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
            ->setEntityLabelInSingular('Experience')
            ->setEntityLabelInPlural('Experience')
            ->setPageTitle(Crud::PAGE_INDEX, 'Experience')
            ->setSearchFields(['company', 'role', 'description'])
            ->setDefaultSort(['sortOrder' => 'ASC', 'startDate' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setAutofocusSearch();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('published'))
            ->add(BooleanFilter::new('current'))
            ->add(TextFilter::new('company'))
            ->add(TextFilter::new('role'))
            ->add(DateTimeFilter::new('startDate'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addColumn(8);
        yield TextField::new('company')->setRequired(true)->setMaxLength(180);
        yield TextField::new('role')->setRequired(true)->setMaxLength(180);
        yield TextareaField::new('description')
            ->setRequired(true)
            ->setHelp('Short summary of the role.')
            ->hideOnIndex();
        yield ArrayField::new('bullets')
            ->setHelp('Key achievements — one per entry.')
            ->hideOnIndex();
        yield AssociationField::new('stacks')
            ->setRequired(false)
            ->autocomplete()
            ->setFormTypeOption('by_reference', false)
            ->setHelp('Select existing technologies. Create new ones under Stack first.')
            ->formatValue(static function ($value, Experience $entity): string {
                return implode(', ', $entity->getStacks()->map(static fn ($s) => $s->getName())->toArray());
            });

        yield FormField::addColumn(4);
        yield DateField::new('startDate', 'Start date')->setRequired(true);
        yield DateField::new('endDate', 'End date')
            ->setRequired(false)
            ->setHelp('Leave empty when the role is current.');
        yield BooleanField::new('current')
            ->renderAsSwitch(true)
            ->setHelp('Mark as your current position.');
        yield BooleanField::new('published')
            ->renderAsSwitch(true)
            ->setHelp('Only published entries appear publicly.');
        yield IntegerField::new('sortOrder', 'Sort order')
            ->setHelp('Lower numbers appear first.')
            ->setFormTypeOption('attr', ['min' => 0]);
        yield TextField::new('period')
            ->onlyOnIndex()
            ->setLabel('Period');
    }
}
