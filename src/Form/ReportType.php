<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Equipment;
use App\Enum\IncidentWaitReason;
use App\Entity\Zone;
use App\Enum\ReportSource;
use App\Enum\ReportStatus;
use App\Entity\Report;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
            ->add('status', ChoiceType::class, [
                'choices' => array_combine(array_map(static fn (ReportStatus $status) => $status->value, ReportStatus::cases()), array_map(static fn (ReportStatus $status) => $status->value, ReportStatus::cases())),
                'label' => 'Statut',
                'data' => ReportStatus::NEW->value,
            ])
            ->add('waitingReason', ChoiceType::class, [
                'choices' => array_combine(array_map(static fn (IncidentWaitReason $reason) => $reason->value, IncidentWaitReason::cases()), array_map(static fn (IncidentWaitReason $reason) => $reason->value, IncidentWaitReason::cases())),
                'required' => false,
                'placeholder' => 'Aucun motif d’attente',
                'label' => 'Motif d’attente',
            ])
            ->add('source', ChoiceType::class, [
                'choices' => array_combine(array_map(static fn (ReportSource $source) => $source->value, ReportSource::cases()), array_map(static fn (ReportSource $source) => $source->value, ReportSource::cases())),
                'label' => 'Source',
                'data' => ReportSource::MOBILE->value,
            ])
            ->add('impactScore', ChoiceType::class, [
                'label' => 'Impact visiteur',
                'choices' => [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5],
            ])
            ->add('urgencyScore', ChoiceType::class, [
                'label' => 'Urgence',
                'choices' => [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5],
            ])
            ->add('aggravationScore', ChoiceType::class, [
                'label' => 'Risque aggravation',
                'choices' => [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5],
            ])
            ->add('servicePilot', TextType::class, [
                'label' => 'Service pilote',
                'required' => false,
            ])
            ->add('supportServices', CollectionType::class, [
                'label' => 'Services supports',
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'entry_type' => TextType::class,
            ])
            ->add('dueAt', DateTimeType::class, [
                'label' => 'Échéance',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
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
