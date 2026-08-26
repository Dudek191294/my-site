<?php

namespace App\Controller;

use App\Portfolio\ProjectProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class ProjectController extends AbstractController
{
    #[Route('/projects/{slug}', name: 'project_show', requirements: ['slug' => '[a-z0-9\-]+'])]
    public function show(string $slug, ProjectProvider $projects): Response
    {
        $project = $projects->findBySlug($slug);

        if ($project === null) {
            throw new NotFoundHttpException(sprintf('Project "%s" not found.', $slug));
        }

        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }
}
