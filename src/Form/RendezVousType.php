<?php

namespace App\Form;

use App\Entity\Medecin;
use App\Entity\Patient;
use App\Entity\RendezVous;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class RendezVousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date du rendez-vous',
                'attr' => [
                    'class' => 'datepicker',
                    'placeholder' => 'Sélectionnez une date'
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut du rendez-vous',
                'choices' => [
                    'En attente' => 'en_attente',
                    'Confirmé' => 'confirme',
                    'Annulé' => 'annule',
                ],
                'expanded' => false, // pour utiliser des boutons radios
                'multiple' => false, // pour autoriser une seule option
            ])
            ->add('motif', null, [
                'label' => 'Motif',
                'attr' => ['placeholder' => 'Motif du rendez-vous'],
            ])
            ->add('date_creation', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de création',
                'attr' => [
                    'class' => 'datepicker',
                    'placeholder' => 'Date de création'
                ],
            ])
            ->add('patient', EntityType::class, [
                'class' => Patient::class,
                'choice_label' => 'nom', // Choisir un champ comme nom complet par exemple
                'label' => 'Sélectionner un patient',
                'placeholder' => 'Choisir un patient',
            ])
            ->add('medecin', EntityType::class, [
                'class' => Medecin::class,
                'choice_label' => 'nom', // Utiliser le nom complet ou un autre champ pertinent
                'label' => 'Sélectionner un médecin',
                'placeholder' => 'Choisir un médecin',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class,
        ]);
    }
}
