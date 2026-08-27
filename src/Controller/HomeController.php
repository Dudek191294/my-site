<?php

namespace App\Controller;

use App\Repository\ExperienceRepository;
use App\Repository\ProjectRepository;
use App\Repository\StackRepository;
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
    ): Response {
        return $this->render('home/index.html.twig', [
            'projects' => $projects->findPublished(),
            'experience' => $experience->findPublishedOrdered(),
            'stack' => $stacks->findPublishedGroupedByCategory(),
        ]);
    }
}
