<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FrontControllerRdv extends AbstractController
{
    #[Route('/front_rdv', name: 'app_front_rdv')]
    public function index(): Response
    {
        return $this->render('rd/front/index.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }
}
