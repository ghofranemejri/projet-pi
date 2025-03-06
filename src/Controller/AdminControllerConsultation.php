<?php

namespace App\Controller;

use App\Repository\ConsultationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminControllerConsultation extends AbstractController
{
    #[Route('/dashboard_Consultation', name: 'app_admin_dashboard_consultation')]
    public function dashboard(ConsultationRepository $consultationRepository): Response
    {
        // Get frequent patients
        $frequentPatients = $consultationRepository->findMostFrequentPatients();

        // Get today's consultations count
        $today = (new \DateTime('today'))->format('Y-m-d 00:00:00');
        $tomorrow = (new \DateTime('tomorrow'))->format('Y-m-d 00:00:00');
        $todayCount = $consultationRepository->getConsultationsCountByPeriod($today, $tomorrow);

        // Get this month's consultations count
        $firstDayOfMonth = (new \DateTime('first day of this month'))->format('Y-m-d 00:00:00');
        $firstDayOfNextMonth = (new \DateTime('first day of next month'))->format('Y-m-d 00:00:00');
        $monthCount = $consultationRepository->getConsultationsCountByPeriod($firstDayOfMonth, $firstDayOfNextMonth);

        return $this->render('consultation/admin/dashboard.html.twig', [
            'frequent_patients' => $frequentPatients,
            'today_count' => $todayCount,
            'month_count' => $monthCount
        ]);
    }
}
