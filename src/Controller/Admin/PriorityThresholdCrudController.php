<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\PriorityThreshold;
use App\Enum\ReportPriority;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class PriorityThresholdCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PriorityThreshold::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Seuil de priorité')
            ->setEntityLabelInPlural('Seuils de priorité')
            ->setDefaultSort(['minScore' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('name'),
            IntegerField::new('minScore'),
            IntegerField::new('maxScore'),
            ChoiceField::new('priority')->setChoices([
                'Faible' => ReportPriority::LOW,
                'Moyenne' => ReportPriority::MEDIUM,
                'Haute' => ReportPriority::HIGH,
                'Critique' => ReportPriority::CRITICAL,
            ]),
        ];
    }
}
