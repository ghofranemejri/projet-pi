<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BackController extends AbstractController
{
    #[Route('/back', name: 'app_back')]
    public function index(): Response
    {
        return $this->render('back/index.html.twig', [
            'controller_name' => 'BackController',
        ]);
    }

    #[Route('/admin/back/users', name: 'admin_back_users')]
    public function adminBackUsers(): Response
    {
        return $this->render('back/admin_users.html.twig', [
            'controller_name' => 'BackController',
        ]);
    }
}
