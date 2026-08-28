<?php

namespace App\Controller\Admin;

use App\Dto\Admin\StackQuickCreateInput;
use App\Entity\Stack;
use App\Repository\StackRepository;
use App\Service\Admin\StackAutocompleteRenderer;
use App\Service\Icon\SimpleIconImporter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted('ROLE_ADMIN')]
final class StackQuickCreateController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StackRepository $stackRepository,
        private readonly SimpleIconImporter $iconImporter,
        private readonly StackAutocompleteRenderer $autocompleteRenderer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('/admin/api/stack/quick-create', name: 'admin_stack_quick_create', methods: ['POST'])]
    public function __invoke(
        Request $request,
        #[MapRequestPayload] StackQuickCreateInput $input,
    ): JsonResponse {
        if (!$this->isCsrfTokenValid('stack_quick_create', (string) $request->headers->get('X-Stack-Quick-Create-Token', ''))) {
            return $this->json(['error' => 'Nieprawidłowy token bezpieczeństwa. Odśwież stronę i spróbuj ponownie.'], Response::HTTP_FORBIDDEN);
        }

        $violations = $this->validator->validate($input);
        if (\count($violations) > 0) {
            return $this->json([
                'error' => $violations->get(0)->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $name = trim($input->name);
        $existing = $this->stackRepository->findOneByName($name);
        if ($existing !== null) {
            return $this->json([
                'entityId' => (string) $existing->getId(),
                'entityAsString' => $this->autocompleteRenderer->render($existing),
                'entityGroup' => $existing->getCategory()->label(),
                'existing' => true,
                'message' => sprintf('Technologia „%s” już istnieje — została przypisana z listy.', $name),
            ]);
        }

        $stack = new Stack();
        $stack->setName($name);
        $stack->setCategory($input->category ?? StackCategory::Tools);
        $stack->setPublished(true);
        $stack->setFeatured(false);
        $stack->setSortOrder(0);

        $icon = $input->icon !== null ? strtolower(trim($input->icon)) : null;
        if ($icon === '') {
            $icon = null;
        }
        $stack->setIcon($icon);

        if ($icon !== null) {
            $report = $this->iconImporter->import($icon);
            if ($report->hasErrors()) {
                return $this->json([
                    'error' => $report->firstError() ?? 'Nie udało się zaimportować ikony.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $this->entityManager->persist($stack);
        $this->entityManager->flush();

        return $this->json([
            'entityId' => (string) $stack->getId(),
            'entityAsString' => $this->autocompleteRenderer->render($stack),
            'entityGroup' => $stack->getCategory()->label(),
        ], Response::HTTP_CREATED);
    }
}
