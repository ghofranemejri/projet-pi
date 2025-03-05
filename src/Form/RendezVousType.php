<?php

namespace App\Form;

use App\Entity\RendezVous;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityRepository;

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
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('motif', TextType::class, [
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
                'class' => User::class,
                'choice_label' => 'email', // You could use 'nom' if available
                'label' => 'Sélectionner un patient',
                'placeholder' => 'Choisir un patient',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%"ROLE_PATIENT"%');
                },
            ])
            ->add('medecin', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email', 
                'label' => 'Sélectionner un médecin',
                'placeholder' => 'Choisir un médecin',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%"ROLE_MEDECIN"%');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class,
        ]);
    }
}
