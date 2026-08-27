<?php

namespace App\Controller\Admin;

use App\Entity\Project;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
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
            ->setEntityLabelInSingular('projekt')
            ->setEntityLabelInPlural('Projekty')
            ->setPageTitle(Crud::PAGE_INDEX, 'Projekty')
            ->setPageTitle(Crud::PAGE_NEW, 'Nowy projekt')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edytuj projekt')
            ->setSearchFields(['title', 'slug', 'shortDescription', 'role'])
            ->setDefaultSort(['sortOrder' => 'ASC', 'id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setAutofocusSearch();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('published', 'Opublikowany'))
            ->add(BooleanFilter::new('featured', 'Wyróżniony'))
            ->add(TextFilter::new('title', 'Tytuł'))
            ->add(TextFilter::new('slug', 'Slug'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab('Ogólne');
        yield FormField::addColumn(8);
        yield TextField::new('title', 'Tytuł')
            ->setRequired(true)
            ->setMaxLength(180)
            ->setHelp('Publiczna nazwa projektu.');
        yield SlugField::new('slug', 'Slug')
            ->setTargetFieldName('title')
            ->setRequired(true)
            ->setHelp('Segment adresu URL, np. moja-aplikacja-portfolio.');
        yield TextareaField::new('shortDescription', 'Krótki opis')
            ->setRequired(true)
            ->setHelp('Tylko karta na stronie głównej — 1–2 zdania. Nie jest powtarzany na stronie projektu.')
            ->hideOnIndex();
        yield TextareaField::new('description', 'Opis projektu')
            ->setRequired(true)
            ->setHelp('Jedyna dłuższa treść na stronie projektu.')
            ->hideOnIndex();
        yield TextField::new('role', 'Rola')
            ->setRequired(false)
            ->setHelp('Opcjonalnie. Zostaw puste, jeśli nie chcesz wyróżniać osobnej roli (np. przy pracy fullstack).')
            ->hideOnIndex();

        yield FormField::addColumn(4);
        yield BooleanField::new('published', 'Opublikowany')
            ->renderAsSwitch(true)
            ->setHelp('Na stronie publicznej widać tylko opublikowane projekty.');
        yield BooleanField::new('featured', 'Wyróżniony')
            ->renderAsSwitch(true)
            ->setHelp('Wyróżnij na liście projektów.');
        yield IntegerField::new('sortOrder', 'Kolejność')
            ->setHelp('Niższe liczby pojawiają się wcześniej.')
            ->setFormTypeOption('attr', ['min' => 0]);
        yield DateTimeField::new('createdAt', 'Utworzono')
            ->hideOnForm()
            ->setFormat('short', 'short');
        yield DateTimeField::new('updatedAt', 'Zaktualizowano')
            ->hideOnForm()
            ->hideOnIndex()
            ->setFormat('short', 'short');

        yield FormField::addTab('Linki i media');
        yield UrlField::new('demoUrl', 'URL demo')
            ->setRequired(false)
            ->hideOnIndex();
        yield UrlField::new('githubUrl', 'URL GitHub')
            ->setRequired(false)
            ->hideOnIndex();
        yield ImageField::new('image', 'Obraz')
            ->setRequired(false)
            ->setBasePath('uploads/projects')
            ->setUploadDir('public/uploads/projects')
            ->setUploadedFileNamePattern('[slug]-[contenthash].[extension]')
            ->mimeTypes('image/jpeg,image/png,image/webp,image/gif', 'Wgraj obraz JPG, PNG, WebP albo GIF.')
            ->maxSize('8M', 'Obraz może mieć maksymalnie 8 MB.')
            ->setHelp('Przeciągnij plik tutaj albo kliknij „Dodaj plik”.')
            ->hideOnIndex();
        yield TextField::new('imageAlt', 'Tekst alternatywny obrazu')
            ->setRequired(false)
            ->hideOnIndex();

        yield FormField::addTab('Stack');
        yield AssociationField::new('stacks', 'Technologie')
            ->setRequired(false)
            ->autocomplete()
            ->setFormTypeOption('by_reference', false)
            ->setHelp('Wybierz istniejące technologie. Nowe dodaj najpierw w module Stack.')
            ->formatValue(static function ($value, Project $entity): string {
                return implode(', ', $entity->getStacks()->map(static fn ($s) => $s->getName())->toArray());
            });

        yield FormField::addTab('Studium przypadku');
        yield TextareaField::new('challenge', 'Wyzwanie')
            ->setRequired(false)
            ->setHelp('Opcjonalnie. Pokazywane na stronie projektu tylko gdy wypełnione.')
            ->hideOnIndex();
        yield TextareaField::new('solution', 'Rozwiązanie')
            ->setRequired(false)
            ->setHelp('Opcjonalnie. Pokazywane na stronie projektu tylko gdy wypełnione.')
            ->hideOnIndex();
    }
}
