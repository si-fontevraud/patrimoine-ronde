<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ControlPoint;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

final class ControlPointCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ControlPoint::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Point de contrôle')
            ->setEntityLabelInPlural('Points de contrôle')
            ->setDefaultSort(['sortOrder' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('reference')->setFormTypeOption('disabled', true),
            AssociationField::new('zone'),
            AssociationField::new('equipment')->setRequired(false),
            TextField::new('label'),
            TextareaField::new('instruction')->setRequired(false),
            TextField::new('frequency'),
            IntegerField::new('sortOrder'),
            IntegerField::new('defaultCriticality'),
            BooleanField::new('isActive'),
            DateTimeField::new('createdAt')->hideOnForm(),
            DateTimeField::new('updatedAt')->hideOnForm(),
        ];
    }
}

