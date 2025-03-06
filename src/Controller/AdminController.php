<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    // Liste des utilisateurs (BACK-OFFICE)
    #[Route('/admin/back/users', name: 'admin_back_users')]
    public function listUsers(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('back/admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    // Promotion d'un utilisateur (BACK-OFFICE)
    #[Route('/admin/back/user/{id}/promote/{role}', name: 'admin_back_user_promote')]
    public function promoteUser(User $user, string $role, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Correction du rôle ROLE_PATIENT
        $validRoles = ['ROLE_MEDECIN', 'ROLE_ADMIN', 'ROLE_PATIENT'];
        if (!in_array($role, $validRoles, true)) {
            throw $this->createNotFoundException('Invalid role.');
        }

        $user->setRoles([$role]);
        $entityManager->flush();

        return $this->redirectToRoute('admin_back_users');
    }

    // Profil utilisateur (FRONT-OFFICE)
    #[Route('/user/profile', name: 'user_profile')]
    public function userProfile(): Response
    {
        $this->denyAccessUnlessGranted(['ROLE_MEDECIN', 'ROLE_MEDECIN', 'ROLE_ADMIN']);

        return $this->render('front/user/profile.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}
