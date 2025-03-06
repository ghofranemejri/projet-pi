<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;

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
            // Ajouter un champ pour la reCAPTCHA (client-side widget)
            ->add('recaptcha', TextType::class, [
                'label' => false,    // Ne pas afficher de label
                'mapped' => false,   // Ne pas lier ce champ à une propriété d'entité
                'attr' => ['class' => 'g-recaptcha'],  // Classe pour le widget reCAPTCHA
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Se connecter',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,  // Protection CSRF activée
            'csrf_field_name' => '_csrf_token',  // Nom du champ CSRF
            'csrf_token_id' => 'authenticate',  // Identifiant du token CSRF
        ]);
    }
}
