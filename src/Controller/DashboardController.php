<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function adminDashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('dashboard/admin.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/medecin/dashboard', name: 'medecin_dashboard')]
    public function medecinDashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MEDECIN');

        return $this->render('dashboard/medecin.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/patient/dashboard', name: 'patient_dashboard')]
    public function patientDashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PATIENT');

        return $this->render('dashboard/patient.html.twig', [
            'user' => $this->getUser()
        ]);
    }
}