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
            ->setEntityLabelInSingular('Social link')
            ->setEntityLabelInPlural('Social links')
            ->setPageTitle(Crud::PAGE_INDEX, 'Social links')
            ->setSearchFields(['label', 'platform', 'url'])
            ->setDefaultSort(['sortOrder' => 'ASC'])
            ->setPaginatorPageSize(20)
            ->setAutofocusSearch();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('published'))
            ->add(ChoiceFilter::new('platform')->setChoices($this->platformChoices()))
            ->add(TextFilter::new('label'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addColumn(8);
        yield ChoiceField::new('platform')
            ->setRequired(true)
            ->setChoices($this->platformChoices())
            ->renderAsNativeWidget()
            ->setHelp('One link per platform (unique).');
        yield TextField::new('label')
            ->setRequired(true)
            ->setMaxLength(80)
            ->setFormTypeOption('attr', ['placeholder' => 'GitHub']);
        yield TextField::new('url', 'URL')
            ->setRequired(true)
            ->setHelp('Full URL or mailto: address.')
            ->setFormTypeOption('attr', ['placeholder' => 'https://github.com/you']);
        yield TextField::new('icon')
            ->setRequired(false)
            ->setHelp('Icon identifier/slug, not an SVG.')
            ->hideOnIndex();

        yield FormField::addColumn(4);
        yield BooleanField::new('published')->renderAsSwitch(true);
        yield IntegerField::new('sortOrder', 'Sort order')
            ->setHelp('Lower numbers appear first.')
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
            'Email' => 'email',
            'X / Twitter' => 'x',
            'Other' => 'other',
        ];
    }
}
