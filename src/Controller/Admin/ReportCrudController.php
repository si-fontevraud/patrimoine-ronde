<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Report;
use App\Enum\IncidentWaitReason;
use App\Enum\ReportPriority;
use App\Enum\ReportStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class ReportCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Report::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Incident')
            ->setEntityLabelInPlural('Incidents')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('reference')->setFormTypeOptions(['disabled' => true]),
            AssociationField::new('zone'),
            AssociationField::new('equipment')->setRequired(false),
            AssociationField::new('author'),
            AssociationField::new('assignedTo')->setRequired(false),
            TextField::new('title'),
            TextareaField::new('description'),
            ChoiceField::new('priority')->setChoices([
                'Faible' => ReportPriority::LOW,
                'Moyenne' => ReportPriority::MEDIUM,
                'Haute' => ReportPriority::HIGH,
                'Critique' => ReportPriority::CRITICAL,
            ]),
            ChoiceField::new('status')->setChoices([
                'Nouveau' => ReportStatus::NEW,
                'Qualifié' => ReportStatus::QUALIFIED,
                'Affecté' => ReportStatus::ASSIGNED,
                'En cours' => ReportStatus::IN_PROGRESS,
                'En attente' => ReportStatus::ON_HOLD,
                'Résolu' => ReportStatus::RESOLVED,
                'Clos' => ReportStatus::CLOSED,
            ]),
            ChoiceField::new('waitingReason')
                ->setRequired(false)
                ->setChoices([
                    'En attente IT' => IncidentWaitReason::IT->value,
                    'En attente technique' => IncidentWaitReason::TECHNICAL->value,
                    'En attente prestataire' => IncidentWaitReason::VENDOR->value,
                    'Autre attente' => IncidentWaitReason::OTHER->value,
                ]),
            IntegerField::new('impactScore'),
            IntegerField::new('urgencyScore'),
            IntegerField::new('aggravationScore'),
            IntegerField::new('totalScore')->hideOnForm(),
            TextField::new('servicePilot')->setRequired(false),
            ArrayField::new('supportServices')->setRequired(false),
            DateTimeField::new('observedAt'),
            DateTimeField::new('dueAt')->setRequired(false),
            DateTimeField::new('createdAt')->hideOnForm(),
            DateTimeField::new('updatedAt')->hideOnForm(),
            DateTimeField::new('resolvedAt')->setRequired(false),
            DateTimeField::new('closedAt')->setRequired(false),
        ];
    }
}
