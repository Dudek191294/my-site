<?php

namespace App\Controller;

use App\Portfolio\ExperienceProvider;
use App\Portfolio\ProjectProvider;
use App\Portfolio\StackProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(
        ProjectProvider $projects,
        ExperienceProvider $experience,
        StackProvider $stack,
    ): Response {
        return $this->render('home/index.html.twig', [
            'projects' => $projects->all(),
            'experience' => $experience->all(),
            'stack' => $stack->categories(),
        ]);
    }
}
