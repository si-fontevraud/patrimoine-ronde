<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\PatrolRound;
use App\Enum\PatrolRoundStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

final class PatrolRoundCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PatrolRound::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Ronde')
            ->setEntityLabelInPlural('Rondes')
            ->setDefaultSort(['startedAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('reference')->setFormTypeOption('disabled', true),
            TextField::new('type'),
            AssociationField::new('agent'),
            ChoiceField::new('status')->setChoices(array_combine(
                array_map(static fn (PatrolRoundStatus $status) => $status->value, PatrolRoundStatus::cases()),
                array_map(static fn (PatrolRoundStatus $status) => $status->value, PatrolRoundStatus::cases()),
            )),
            IntegerField::new('totalPoints'),
            DateTimeField::new('startedAt'),
            DateTimeField::new('finishedAt')->setRequired(false),
            TextareaField::new('generalObservation')->setRequired(false),
            DateTimeField::new('createdAt')->hideOnForm(),
        ];
    }
}

