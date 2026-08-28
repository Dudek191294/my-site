<?php

namespace App\Service\Admin;

use App\Controller\Admin\StackCrudController;
use App\Entity\Stack;
use App\Entity\StackCategory;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class StackAssociationFieldConfigurator
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    public function configure(AssociationField $field): AssociationField
    {
        $categories = [];
        foreach (StackCategory::cases() as $category) {
            $categories[] = [
                'value' => $category->value,
                'label' => $category->label(),
            ];
        }

        return $field
            ->setRequired(false)
            ->autocomplete(
                template: 'admin/autocomplete/stack.html.twig',
                renderAsHtml: true,
            )
            ->setCrudController(StackCrudController::class)
            ->setQueryBuilder(static function ($queryBuilder) {
                return $queryBuilder
                    ->orderBy('entity.category', 'ASC')
                    ->addOrderBy('entity.sortOrder', 'ASC')
                    ->addOrderBy('entity.name', 'ASC');
            })
            ->setFormTypeOption('group_by', static fn (Stack $stack): string => $stack->getCategory()->label())
            ->setFormTypeOption('by_reference', false)
            ->addCssClass('field-stack-association')
            ->setFormTypeOption('attr', [
                'data-stack-field' => '1',
                'data-stack-quick-create-url' => $this->urlGenerator->generate('admin_stack_quick_create'),
                'data-stack-quick-create-token' => $this->csrfTokenManager->getToken('stack_quick_create')->getValue(),
                'data-stack-categories' => json_encode($categories, \JSON_THROW_ON_ERROR),
            ])
            ->setHelp('Wyszukaj i wybierz technologie. Możesz też szybko dodać nową poniżej — bez przechodzenia do modułu Stack.');
    }
}
