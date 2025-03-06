<?php

namespace App\Form;

use App\Entity\Prescription;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class PrescriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('datedeb', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'La date de début est obligatoire.']),
                ],
            ])
            ->add('datefin', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'La date de fin est obligatoire.']),
                    new GreaterThanOrEqual([
                        'propertyPath' => 'parent.all[datedeb].data',
                        'message' => 'La date de fin doit être supérieure ou égale à la date de début.'
                    ]),
                ],
            ])
            ->add('address', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'L\'adresse est obligatoire.']),
                ],
            ])
            ->add('patient', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => function (Utilisateur $user) {
                    return $user->getNom() . ' ' . $user->getPrenom();
                },
                'placeholder' => 'Sélectionnez un patient',
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner un patient.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prescription::class,
        ]);
    }
}
