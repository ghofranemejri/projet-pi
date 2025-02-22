<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            //->add('roles', ChoiceType::class, [
               // 'choices' => [
                    //'Medecin' => 'ROLE_MEDECIN',
                    //'Admin' => 'ROLE_ADMIN',
                //],
                //'expanded' => true,  // Affiche les choix sous forme de cases à cocher
                //'multiple' => true, // Permet de choisir plusieurs rôles
               // 'data' => $options['data']->getRoles() ?? [], // Pré-sélectionner les rôles déjà associés
           // ])
            ->add('password', PasswordType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
