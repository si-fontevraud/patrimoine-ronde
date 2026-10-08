<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\PatrolResult;
use App\Enum\PatrolCheckResult;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

final class PatrolResultCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PatrolResult::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Résultat de ronde')
            ->setEntityLabelInPlural('Résultats de ronde')
            ->setDefaultSort(['checkedAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            AssociationField::new('patrolRound'),
            AssociationField::new('controlPoint'),
            AssociationField::new('equipment')->setRequired(false),
            AssociationField::new('report')->setRequired(false),
            ChoiceField::new('result')->setChoices([
                'OK' => PatrolCheckResult::OK,
                'Observation' => PatrolCheckResult::OBSERVATION,
                'Incident' => PatrolCheckResult::INCIDENT,
                'Non contrôlé' => PatrolCheckResult::NOT_CHECKED,
            ]),
            TextareaField::new('comment')->setRequired(false),
            TextField::new('photoPath')->setRequired(false),
            DateTimeField::new('checkedAt'),
        ];
    }
}
