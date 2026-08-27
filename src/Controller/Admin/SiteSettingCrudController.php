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
            ->setEntityLabelInSingular('Site settings')
            ->setEntityLabelInPlural('Site settings')
            ->setPageTitle(Crud::PAGE_INDEX, 'Site settings')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit site settings')
            ->setPageTitle(Crud::PAGE_NEW, 'Create site settings')
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
        yield FormField::addTab('Brand & hero');
        yield TextField::new('siteName', 'Site name')
            ->setRequired(true)
            ->setHelp('Used in navigation, footer and page titles.');
        yield TextField::new('roleTitle', 'Role title')
            ->setRequired(true)
            ->setHelp('Hero eyebrow, e.g. Full-Stack Developer.');
        yield TextField::new('headline')
            ->setRequired(true);
        yield TextareaField::new('positioning')
            ->setRequired(true)
            ->setHelp('One or two sentences under the headline.')
            ->hideOnIndex();

        yield FormField::addTab('About');
        yield TextareaField::new('aboutBody', 'About body')
            ->setRequired(true)
            ->setHelp('Separate paragraphs with a blank line.')
            ->hideOnIndex();
        yield TextField::new('location')->setRequired(false);
        yield TextField::new('availability')->setRequired(false);
        yield TextField::new('workMode', 'Work mode')
            ->setRequired(false)
            ->setHelp('e.g. Remote / Hybrid')
            ->setFormTypeOption('attr', ['placeholder' => 'Remote / Hybrid']);
        yield ArrayField::new('principles')
            ->setHelp('Working principles shown in About.')
            ->hideOnIndex();

        yield FormField::addTab('Contact & SEO');
        yield TextareaField::new('contactIntro', 'Contact intro')
            ->setRequired(false)
            ->hideOnIndex();
        yield EmailField::new('contactEmail', 'Contact email')
            ->setRequired(false);
        yield TextField::new('metaDescription', 'Meta description')
            ->setRequired(false)
            ->setMaxLength(300)
            ->hideOnIndex();
        yield DateTimeField::new('updatedAt')
            ->hideOnForm()
            ->setFormat('short', 'medium');
    }

    public function createEntity(string $entityFqcn): SiteSetting
    {
        if ($this->siteSettingRepository->count([]) > 0) {
            throw $this->createAccessDeniedException('Site settings already exist. Edit the existing row instead.');
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
