<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted('ROLE_ADMIN')]
final class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Portfolio');
        yield MenuItem::linkTo(ProjectCrudController::class, 'Projects', 'fa fa-folder-open');
        yield MenuItem::linkTo(StackCrudController::class, 'Stack', 'fa fa-code');
        yield MenuItem::linkTo(ExperienceCrudController::class, 'Experience', 'fa fa-briefcase');

        yield MenuItem::section('Site');
        yield MenuItem::linkTo(SocialLinkCrudController::class, 'Social links', 'fa fa-share-nodes');
        yield MenuItem::linkTo(SiteSettingCrudController::class, 'Settings', 'fa fa-gear');
    }
}
