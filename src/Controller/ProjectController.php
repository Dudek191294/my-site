<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use App\Repository\SiteSettingRepository;
use App\Repository\SocialLinkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class ProjectController extends AbstractController
{
    #[Route('/projects/{slug}', name: 'project_show', requirements: ['slug' => '[a-z0-9\-]+'])]
    public function show(
        string $slug,
        ProjectRepository $projects,
        SiteSettingRepository $siteSettings,
        SocialLinkRepository $socialLinks,
    ): Response {
        $project = $projects->findPublishedBySlug($slug);

        if ($project === null) {
            throw new NotFoundHttpException(sprintf('Project "%s" not found.', $slug));
        }

        return $this->render('project/show.html.twig', [
            'project' => $project,
            'site' => $siteSettings->findSingleton(),
            'socialLinks' => $socialLinks->findPublishedOrdered(),
        ]);
    }
}
