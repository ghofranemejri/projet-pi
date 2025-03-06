<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class dashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard_home')]
    public function dashboard(): Response
    {
        $user = $this->getUser();

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('admin_dashboard');
        } elseif (in_array('ROLE_MEDECIN', $user->getRoles())) {
            return $this->redirectToRoute('medecin_dashboard');
        } elseif (in_array('ROLE_PATIENT', $user->getRoles())) {
            return $this->redirectToRoute('patient_dashboard');
        }

        // Redirection par défaut si l'utilisateur n'a aucun rôle reconnu
        return $this->redirectToRoute('home');
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function admindashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('dashboard/admin.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/medecin/dashboard', name: 'medecin_dashboard')]
    public function medecindashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MEDECIN');

        return $this->render('dashboard/medecin.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/patient/dashboard', name: 'patient_dashboard')]
    public function patientdashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PATIENT');

        return $this->render('dashboard/patient.html.twig', [
            'user' => $this->getUser()
        ]);
    }
}