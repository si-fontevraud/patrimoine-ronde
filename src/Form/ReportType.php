<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Equipment;
use App\Entity\Zone;
use App\Enum\ReportPriority;
use App\Enum\ReportSource;
use App\Enum\ReportStatus;
use App\Entity\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre'])
            ->add('description', TextareaType::class, ['label' => 'Description'])
            ->add('zone', EntityType::class, [
                'class' => Zone::class,
                'choice_label' => 'name',
                'label' => 'Zone',
                'placeholder' => 'Choisir une zone',
            ])
            ->add('equipment', EntityType::class, [
                'class' => Equipment::class,
                'choice_label' => 'name',
                'label' => 'Équipement (optionnel)',
                'required' => false,
                'placeholder' => 'Aucun équipement',
            ])
            ->add('priority', ChoiceType::class, [
                'choices' => array_combine(array_map(static fn (ReportPriority $priority) => $priority->value, ReportPriority::cases()), array_map(static fn (ReportPriority $priority) => $priority->value, ReportPriority::cases())),
                'label' => 'Priorité',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => array_combine(array_map(static fn (ReportStatus $status) => $status->value, ReportStatus::cases()), array_map(static fn (ReportStatus $status) => $status->value, ReportStatus::cases())),
                'label' => 'Statut',
                'data' => ReportStatus::NEW->value,
            ])
            ->add('source', ChoiceType::class, [
                'choices' => array_combine(array_map(static fn (ReportSource $source) => $source->value, ReportSource::cases()), array_map(static fn (ReportSource $source) => $source->value, ReportSource::cases())),
                'label' => 'Source',
                'data' => ReportSource::MOBILE->value,
            ])
            ->add('observedAt', DateTimeType::class, [
                'label' => 'Date constatée',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('photos', FileType::class, [
                'label' => 'Photos',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Report::class,
        ]);
    }
}
