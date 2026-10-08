<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
final class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Patrimoine Ronde')
            ->setFaviconPath('favicon.svg');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::section('Référentiel');
        yield MenuItem::linkTo(ZoneCrudController::class, 'Zones', 'fa fa-map-marker');
        yield MenuItem::linkTo(ControlPointCrudController::class, 'Points de contrôle', 'fa fa-list-check');
        yield MenuItem::linkTo(EquipmentCrudController::class, 'Équipements', 'fa fa-tools');
        yield MenuItem::section('Exploitation');
        yield MenuItem::linkTo(PatrolRoundCrudController::class, 'Rondes', 'fa fa-route');
        yield MenuItem::linkTo(PatrolResultCrudController::class, 'Résultats de ronde', 'fa fa-clipboard-check');
        yield MenuItem::linkTo(ReportCrudController::class, 'Incidents', 'fa fa-bell');
        yield MenuItem::linkTo(ReportInterventionCrudController::class, 'Interventions', 'fa fa-screwdriver-wrench');
        yield MenuItem::linkTo(PriorityThresholdCrudController::class, 'Seuils priorité', 'fa fa-sliders');
        yield MenuItem::section('Administration');
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fa fa-user');
    }
}
