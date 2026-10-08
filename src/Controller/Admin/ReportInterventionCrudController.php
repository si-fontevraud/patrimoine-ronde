<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ReportIntervention;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

final class ReportInterventionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ReportIntervention::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Intervention')
            ->setEntityLabelInPlural('Interventions')
            ->setDefaultSort(['performedAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            AssociationField::new('report'),
            AssociationField::new('actor')->setRequired(false),
            DateTimeField::new('performedAt'),
            TextareaField::new('action'),
            IntegerField::new('durationMinutes')->setRequired(false),
            TextField::new('replacedPart')->setRequired(false),
            TextField::new('result')->setRequired(false),
            TextField::new('attachmentPath')->setRequired(false),
        ];
    }
}

