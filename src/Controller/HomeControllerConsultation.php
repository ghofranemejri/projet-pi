<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeControllerConsultation extends AbstractController
{
    #[Route('/app_home_consultation', name: 'app_home_consultation')]
    public function index(): Response
    {
        return $this->render('Consultation/home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
