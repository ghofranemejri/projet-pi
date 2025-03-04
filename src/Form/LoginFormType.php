<?php

namespace App\Form;

use Karser\Recaptcha3Bundle\Form\Recaptcha3Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['autocomplete' => 'email'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre email.',
                    ]),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'attr' => ['autocomplete' => 'current-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre mot de passe.',
                    ]),
                ],
            ])
            ->add('recaptcha', Recaptcha3Type::class, [
                'constraints' => new \Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3(),
                'action_name' => 'login',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id' => 'authenticate',
        ]);
    }
}
