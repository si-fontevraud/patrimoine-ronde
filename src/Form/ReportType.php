<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Equipment;
use App\Enum\IncidentWaitReason;
use App\Entity\Zone;
use App\Enum\ReportSource;
use App\Enum\ReportStatus;
use App\Entity\Report;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
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
            ->add('status', EnumType::class, [
                'class' => ReportStatus::class,
                'choice_label' => static fn (ReportStatus $status): string => match ($status) {
                    ReportStatus::NEW => 'Nouveau',
                    ReportStatus::QUALIFIED => 'Qualifié',
                    ReportStatus::ASSIGNED => 'Affecté',
                    ReportStatus::IN_PROGRESS => 'En cours',
                    ReportStatus::ON_HOLD => 'En attente',
                    ReportStatus::RESOLVED => 'Résolu',
                    ReportStatus::CLOSED => 'Clos',
                },
                'label' => 'Statut',
                'data' => ReportStatus::NEW,
            ])
            ->add('waitingReason', ChoiceType::class, [
                'choices' => [
                    'En attente IT' => IncidentWaitReason::IT->value,
                    'En attente technique' => IncidentWaitReason::TECHNICAL->value,
                    'En attente prestataire' => IncidentWaitReason::VENDOR->value,
                    'Autre attente' => IncidentWaitReason::OTHER->value,
                ],
                'required' => false,
                'placeholder' => 'Aucun motif d’attente',
                'label' => 'Motif d’attente',
            ])
            ->add('source', EnumType::class, [
                'class' => ReportSource::class,
                'choice_label' => static fn (ReportSource $source): string => match ($source) {
                    ReportSource::WEB => 'Web',
                    ReportSource::MOBILE => 'Mobile',
                    ReportSource::SYNC => 'Synchronisation',
                },
                'label' => 'Source',
                'data' => ReportSource::MOBILE,
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
            ->add('supportServices', ChoiceType::class, [
                'label' => 'Services supports',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'choices' => [
                    'Technique' => 'technique',
                    'Informatique (IT)' => 'it',
                    'Sécurité' => 'securite',
                    'Accueil / Billetterie' => 'accueil',
                    'Prestataire externe' => 'prestataire',
                ],
                'help' => 'Sélectionner un ou plusieurs services supports à solliciter.',
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
                'attr' => [
                    'accept' => 'image/*',
                    'capture' => 'environment',
                ],
                'help' => 'Sur mobile, ouvre l’appareil photo (caméra arrière) selon le navigateur.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Report::class,
        ]);
    }
}
