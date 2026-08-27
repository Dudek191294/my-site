<?php

namespace App\Controller;

use App\Repository\ExperienceRepository;
use App\Repository\ProjectRepository;
use App\Repository\SiteSettingRepository;
use App\Repository\StackRepository;
use App\Repository\SocialLinkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(
        ProjectRepository $projects,
        ExperienceRepository $experience,
        StackRepository $stacks,
        SiteSettingRepository $siteSettings,
        SocialLinkRepository $socialLinks,
    ): Response {
        return $this->render('home/index.html.twig', [
            'projects' => $projects->findPublished(),
            'experience' => $experience->findPublishedOrdered(),
            'stack' => $stacks->findPublishedGroupedByCategory(),
            'site' => $siteSettings->findSingleton(),
            'socialLinks' => $socialLinks->findPublishedOrdered(),
        ]);
    }
}
