<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Report;
use App\Enum\ReportPriority;
use App\Enum\ReportStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
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
            ->setEntityLabelInSingular('Signalement')
            ->setEntityLabelInPlural('Signalements')
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
            ChoiceField::new('priority')->setChoices(array_combine(
                array_map(static fn (ReportPriority $priority) => $priority->value, ReportPriority::cases()),
                array_map(static fn (ReportPriority $priority) => $priority->value, ReportPriority::cases())
            )),
            ChoiceField::new('status')->setChoices(array_combine(
                array_map(static fn (ReportStatus $status) => $status->value, ReportStatus::cases()),
                array_map(static fn (ReportStatus $status) => $status->value, ReportStatus::cases())
            )),
            DateTimeField::new('observedAt'),
            DateTimeField::new('createdAt')->hideOnForm(),
            DateTimeField::new('updatedAt')->hideOnForm(),
            DateTimeField::new('resolvedAt')->setRequired(false),
            DateTimeField::new('closedAt')->setRequired(false),
        ];
    }
}
