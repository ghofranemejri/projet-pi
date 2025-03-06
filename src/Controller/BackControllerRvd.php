<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BackControllerRvd extends AbstractController
{
    #[Route('/back_Rvd', name: 'app_back_Rvd')]
    public function index(): Response
    {
        return $this->render('rd/back/index.html.twig', [
            'controller_name' => 'BackController',
        ]);
    }
}

