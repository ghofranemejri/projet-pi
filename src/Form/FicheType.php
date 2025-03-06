<?php

namespace App\Form;

use App\Entity\Fiche;
use App\Entity\Consultation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FicheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('consultation', EntityType::class, [
                'class' => Consultation::class,
                'choice_label' => function(Consultation $consultation) {
                    return $consultation->getNomPatient() . ' - ' . 
                           $consultation->getDateConsultation()->format('Y-m-d H:i');
                },
                'label' => 'Consultation',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('motif', TextareaType::class, [
                'label' => 'Motif de consultation',
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('diagnostic', TextareaType::class, [
                'label' => 'Diagnostic',
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('traitement', TextareaType::class, [
                'label' => 'Traitement prescrit',
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('nomPatient', TextType::class, [
                'label' => 'Nom du Patient',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom du patient'
                ]
            ])
            ->add('nomMedecin', TextType::class, [
                'label' => 'Nom du Médecin',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom du médecin'
                ]
            ])
            ->add('date', DateTimeType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'En attente' => 'pending',
                    'En cours' => 'in_progress',
                    'Terminée' => 'completed',
                    'Annulée' => 'cancelled'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Fiche::class,
        ]);
    }
}
