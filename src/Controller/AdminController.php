<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_users')]
    public function listUsers(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/user/{id}/promote/{role}', name: 'admin_user_promote')]
    public function promoteUser(User $user, string $role, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Only allow valid role promotions
        $validRoles = ['ROLE_MEDECIN', 'ROLE_ADMIN'];
        if (!in_array($role, $validRoles, true)) {
            throw $this->createNotFoundException('Invalid role.');
        }

        $user->setRoles([$role]);
        $entityManager->flush();

        return $this->redirectToRoute('admin_users');
    }
}
