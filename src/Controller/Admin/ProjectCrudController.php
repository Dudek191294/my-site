<?php

namespace App\Controller\Admin;

use App\Entity\Project;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class ProjectCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Project')
            ->setEntityLabelInPlural('Projects')
            ->setPageTitle(Crud::PAGE_INDEX, 'Projects')
            ->setPageTitle(Crud::PAGE_NEW, 'New project')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit project')
            ->setSearchFields(['title', 'slug', 'shortDescription', 'role'])
            ->setDefaultSort(['sortOrder' => 'ASC', 'id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setAutofocusSearch();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('published'))
            ->add(BooleanFilter::new('featured'))
            ->add(TextFilter::new('title'))
            ->add(TextFilter::new('slug'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab('General');
        yield FormField::addColumn(8);
        yield TextField::new('title')
            ->setRequired(true)
            ->setMaxLength(180)
            ->setHelp('Public project name.');
        yield SlugField::new('slug')
            ->setTargetFieldName('title')
            ->setRequired(true)
            ->setHelp('URL segment, e.g. my-portfolio-app.');
        yield TextareaField::new('shortDescription', 'Short description')
            ->setRequired(true)
            ->setHelp('Shown on the home project cards.')
            ->hideOnIndex();
        yield TextareaField::new('description')
            ->setRequired(true)
            ->setHelp('Longer project description.')
            ->hideOnIndex();
        yield TextField::new('role')
            ->setRequired(true)
            ->setHelp('Your role on this project.')
            ->hideOnIndex();

        yield FormField::addColumn(4);
        yield BooleanField::new('published')
            ->renderAsSwitch(true)
            ->setHelp('Only published projects appear on the public site.');
        yield BooleanField::new('featured')
            ->renderAsSwitch(true)
            ->setHelp('Highlight on the projects listing.');
        yield IntegerField::new('sortOrder', 'Sort order')
            ->setHelp('Lower numbers appear first.')
            ->setFormTypeOption('attr', ['min' => 0]);
        yield DateTimeField::new('createdAt')
            ->hideOnForm()
            ->setFormat('short', 'short');
        yield DateTimeField::new('updatedAt')
            ->hideOnForm()
            ->hideOnIndex()
            ->setFormat('short', 'short');

        yield FormField::addTab('Links & media');
        yield UrlField::new('demoUrl', 'Demo URL')
            ->setRequired(false)
            ->hideOnIndex();
        yield UrlField::new('githubUrl', 'GitHub URL')
            ->setRequired(false)
            ->hideOnIndex();
        yield TextField::new('image')
            ->setRequired(false)
            ->setHelp('Path or URL to the cover image (not an uploaded file yet).')
            ->hideOnIndex();
        yield TextField::new('imageAlt', 'Image alt text')
            ->setRequired(false)
            ->hideOnIndex();

        yield FormField::addTab('Stack');
        yield AssociationField::new('stacks')
            ->setRequired(false)
            ->autocomplete()
            ->setFormTypeOption('by_reference', false)
            ->setHelp('Select existing technologies. Create new ones under Stack first.')
            ->formatValue(static function ($value, Project $entity): string {
                return implode(', ', $entity->getStacks()->map(static fn ($s) => $s->getName())->toArray());
            });

        yield FormField::addTab('Case study');
        yield TextareaField::new('overview')->setRequired(true)->hideOnIndex();
        yield TextareaField::new('problem')->setRequired(true)->hideOnIndex();
        yield TextareaField::new('solution')->setRequired(true)->hideOnIndex();
        yield TextareaField::new('result')->setRequired(true)->hideOnIndex();

        yield FormField::addFieldset('Architecture');
        yield TextareaField::new('architectureFrontend', 'Frontend')->hideOnIndex();
        yield TextareaField::new('architectureApi', 'API')->hideOnIndex();
        yield TextareaField::new('architectureBackend', 'Backend')->hideOnIndex();
        yield TextareaField::new('architectureDatabase', 'Database')->hideOnIndex();
        yield TextareaField::new('architectureInfrastructure', 'Infrastructure')->hideOnIndex();

        yield ArrayField::new('technicalDecisions', 'Technical decisions')
            ->setHelp('One decision per entry.')
            ->hideOnIndex();
        yield ArrayField::new('challenges')
            ->setHelp('One challenge per entry.')
            ->hideOnIndex();
        yield ArrayField::new('lessonsLearned', 'Lessons learned')
            ->setHelp('One lesson per entry.')
            ->hideOnIndex();
    }
}
