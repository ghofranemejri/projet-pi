<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        // Crée une nouvelle instance de l'entité User
        $user = new User();

        // Crée le formulaire d'inscription
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Hash le mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData() // Récupère le mot de passe en clair
            );
            $user->setPassword($hashedPassword); // Définit le mot de passe haché

            // Attribue un rôle par défaut (ROLE_PATIENT)
            $user->setRoles(['ROLE_PATIENT']);

            // Persiste l'utilisateur en base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Ajoute un message flash pour informer l'utilisateur
            $this->addFlash('success', 'Votre compte a été créé avec succès. Veuillez vous connecter.');

            // Redirige vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        // Si le formulaire n'est pas valide, affiche les erreurs
        if ($form->isSubmitted() && !$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        // Affiche le formulaire d'inscription
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}