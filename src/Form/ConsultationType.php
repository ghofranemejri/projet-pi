<?php

namespace App\Form;

use App\Entity\Consultation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsultationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomMedecin', TextType::class, [
                'label' => 'Nom du Médecin',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom du médecin'
                ]
            ])
            ->add('nomPatient', TextType::class, [
                'label' => 'Nom du Patient',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom du patient'
                ]
            ])
            ->add('dateConsultation', DateTimeType::class, [
                'label' => 'Date de Consultation',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Consultation::class,
        ]);
    }
}
