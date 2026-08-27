<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
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
            ->setTitle('Panel administracyjny');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addAssetMapperEntry('admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Pulpit', 'fa fa-home');

        yield MenuItem::section('Portfolio');
        yield MenuItem::linkTo(ProjectCrudController::class, 'Projekty', 'fa fa-folder-open');
        yield MenuItem::linkTo(StackCrudController::class, 'Stack', 'fa fa-code');
        yield MenuItem::linkTo(ExperienceCrudController::class, 'Doświadczenie', 'fa fa-briefcase');

        yield MenuItem::section('Konto');
        yield MenuItem::linkTo(UserCrudController::class, 'Użytkownicy', 'fa fa-user');

        yield MenuItem::section('Strona');
        yield MenuItem::linkTo(SocialLinkCrudController::class, 'Linki społecznościowe', 'fa fa-share-nodes');
        yield MenuItem::linkTo(SiteSettingCrudController::class, 'Ustawienia', 'fa fa-gear');
    }
}
