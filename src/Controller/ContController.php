<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContController extends AbstractController
{
    #[Route('/cont', name: 'app_cont')]
    public function index(): Response
    {
        return $this->render('base.html.twig', [
            'controller_name' => 'ContController',
        ]);
    }
}
