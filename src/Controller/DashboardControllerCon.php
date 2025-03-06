<?php

namespace App\Controller;

use App\Repository\ConsultationRepository;
use App\Repository\FicheRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class DashboardControllerCon extends AbstractController
{
    #[Route('/admin_dashboardd', name: 'admin_dashboardd')]
    public function index(ConsultationRepository $consultationRepository, FicheRepository $ficheRepository): Response
    {
        // Get frequent patients
        $frequentPatients = $consultationRepository->findMostFrequentPatients(5);

        // Get today's date at midnight
        $today = new \DateTime('today');

        // Get statistics
        $stats = [
            'totalConsultations' => $consultationRepository->count([]),
            'todayConsultations' => $consultationRepository->getTodayConsultations($today),
            'totalFiches' => $ficheRepository->count([]),
            'todayFiches' => $ficheRepository->getTodayFiches($today)
        ];

        // Get recent consultations
        $consultations = $consultationRepository->findBy([], ['dateConsultation' => 'DESC'], 5);
        
        // Get recent fiches
        $fiches = $ficheRepository->findBy([], ['date' => 'DESC'], 5);

        return $this->render('consultation/admin/dashboard/index.html.twig', [
            'stats' => $stats,
            'frequent_patients' => $frequentPatients,
            'consultations' => $consultations,
            'fiches' => $fiches
        ]);
    }

    #[Route('/users', name: 'admin_users')]
    public function users(): Response
    {
        return $this->render('admin/users/index.html.twig');
    }

    #[Route('/experts', name: 'admin_experts')]
    public function experts(): Response
    {
        return $this->render('admin/users/experts.html.twig');
    }

    #[Route('/consultations', name: 'admin_consultations')]
    public function consultations(): Response
    {
        return $this->render('admin/consultations/index.html.twig');
    }

    #[Route('/consultation-categories', name: 'admin_consultation_categories')]
    public function consultationCategories(): Response
    {
        return $this->render('admin/consultations/categories.html.twig');
    }

    #[Route('/payments', name: 'admin_payments')]
    public function payments(): Response
    {
        return $this->render('admin/payments/index.html.twig');
    }

    #[Route('/settings', name: 'admin_settings')]
    public function settings(): Response
    {
        return $this->render('admin/settings/index.html.twig');
    }
}
