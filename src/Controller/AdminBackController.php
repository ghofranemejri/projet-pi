<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;


class AdminBackController extends AbstractController
{
    // Afficher le profil utilisateur (FRONT-OFFICE)
    //#[Route('/back/users', name: 'admin_users')]
    // public function userProfile(): Response
    // {
    //     $this->denyAccessUnlessGranted(['ROLE_USER', 'ROLE_MEDECIN', 'ROLE_ADMIN']);

    //     return $this->render('front/user/profile.html.twig', [
    //         'user' => $this->getUser(),
    //     ]);
    // }

    // Afficher la liste des utilisateurs (BACK-OFFICE)
    #[Route('/back/users', name: 'admin_users')]
    public function index(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
    
        $search = $request->query->get('search', '');
        $roleFilter = $request->query->get('role', '');
    
        $queryBuilder = $entityManager->getRepository(User::class)->createQueryBuilder('u');
    
        // Filtrage par email
        if (!empty($search)) {
            $queryBuilder->andWhere('u.email LIKE :search')
                         ->setParameter('search', '%' . $search . '%');
        }
    
        // Filtrage par rôle
        if (!empty($roleFilter)) {
            $queryBuilder->andWhere('u.roles LIKE :role')
                         ->setParameter('role', '%' . $roleFilter . '%');
        }
    
        $query = $queryBuilder->getQuery();
    
        // Pagination
        $users = $paginator->paginate(
            $query, 
            $request->query->getInt('page', 1), // Numéro de la page, par défaut 1
            10 // Nombre d'éléments par page
        );
    
        return $this->render('user_back/users.html.twig', [
            'users' => $users,
            'search' => $search,
            'roleFilter' => $roleFilter,
        ]);
    }
    


    // Afficher les détails d'un utilisateur (BACK-OFFICE)
    #[Route('/admin/back/user/{id}', name: 'admin_back_user_show')]
    public function showUser(int $id, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupérer l'utilisateur par son ID
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        return $this->render('user_back/show.html.twig', [
            'user' => $user,
        ]);
    }

    // Créer un nouvel utilisateur (BACK-OFFICE)
    #[Route('/admin/back/user/new', name: 'admin_back_user_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('admin_back_user_index');
        }

        return $this->render('user_back/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Modifier un utilisateur (BACK-OFFICE)
    #[Route('/admin/back/user/{id}/edit', name: 'admin_back_user_edit')]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_back_user_index');
        }

        return $this->render('user_profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    // Supprimer un utilisateur (BACK-OFFICE)
    #[Route('/admin/back/user/{id}', name: 'admin_back_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Vérifier le token CSRF pour la sécurité
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_back_user_index');
    }
}