<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/users", name="user_list")
     */
    public function index(Request $request): Response
    {
        // Récupère les paramètres de tri de la requête, avec des valeurs par défaut
        $sortBy = $request->query->get('sortBy', 'nom'); // Champ de tri
        $order = $request->query->get('order', 'ASC');  // Ordre de tri (ASC ou DESC)

        // Vérification de l'ordre
        if (!in_array(strtoupper($order), ['ASC', 'DESC'])) {
            $order = 'ASC'; // Défaut si l'ordre est invalide
        }

        // Appelle la méthode pour récupérer les utilisateurs triés
        $users = $this->userRepository->findUsersByRoleSorted('ROLE_USER', $sortBy, $order);

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'sortBy' => $sortBy,
            'order' => $order,
        ]);
    }

    /**
     * @Route("/admin/back/users", name="admin_back_users")
     */
    public function listPatients(Request $request): Response
    {
        // Récupère les paramètres de tri de la requête
        $sortBy = $request->query->get('sortBy', 'nom'); // Champ de tri
        $order = $request->query->get('order', 'ASC');  // Ordre de tri (ASC ou DESC)

        // Vérification de l'ordre
        if (!in_array(strtoupper($order), ['ASC', 'DESC'])) {
            $order = 'ASC'; // Défaut si l'ordre est invalide
        }

        // Appelle la méthode pour récupérer les patients triés
        $patients = $this->userRepository->findPatientsSorted($sortBy, $order);

        return $this->render('user/patient_list.html.twig', [
            'patients' => $patients,
            'sortBy' => $sortBy,
            'order' => $order,
        ]);
    }
}
