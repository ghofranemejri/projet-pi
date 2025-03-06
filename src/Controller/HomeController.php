<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



use App\Repository\PrescriptionRepository;
use App\Repository\TraitementRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;


final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function homepage(): Response
    {
        return $this->render('home/index.html.twig', [
            
        ]);
    }

    #[Route('/back', name: 'app_backpage')]
    public function back(): Response
    {
        return $this->render('baseback.html.twig', [
            
        ]);
    }

    #[Route('/stat', name: 'admin_stat')]
    public function index(
        PrescriptionRepository $prescriptionRepo,
        TraitementRepository $traitementRepo,
        EntityManagerInterface $entityManager
    ): Response {
        // Fetch statistics
        $totalPrescriptions = $prescriptionRepo->count([]);
        $totalTreatments = $traitementRepo->count([]);
    
        // Categorize treatments based on dosage ranges
        $dosageRanges = [
            '0-100' => 0,
            '100-200' => 0,
            '200-500' => 0,
            '1000+' => 0
        ];
    
        $dosageData = $entityManager->createQuery(
            "SELECT t.dose FROM App\Entity\Traitement t"
        )->getResult();
    
        foreach ($dosageData as $treatment) {
            $dose = $treatment['dose'];
            if ($dose <= 100) {
                $dosageRanges['0-100']++;
            } elseif ($dose > 100 && $dose <= 200) {
                $dosageRanges['100-200']++;
            }elseif ($dose > 200 && $dose <= 500) {
                $dosageRanges['200-500']++;
            } else {
                $dosageRanges['1000+']++;
            }
        }
    
        // Get top 5 most prescribed medications
        $topMedications = $entityManager->createQuery(
            "SELECT t.medicament, COUNT(t.id) as total
            FROM App\Entity\Traitement t
            GROUP BY t.medicament
            ORDER BY total DESC"
        )->setMaxResults(5)->getResult();
    
        // Prepare data for the chart
        $medicationLabels = [];
        $medicationCounts = [];
    
        foreach ($topMedications as $med) {
            $medicationLabels[] = $med['medicament'];
            $medicationCounts[] = $med['total'];
        }
    
        return $this->render('stat.html.twig', [
            'totalPrescriptions' => $totalPrescriptions,
            'totalTreatments' => $totalTreatments,
            'dosageLabels' => json_encode(array_keys($dosageRanges)),
            'dosageCounts' => json_encode(array_values($dosageRanges)),
            'medicationLabels' => json_encode($medicationLabels), // Fix: Pass correct variable
            'medicationCounts' => json_encode($medicationCounts)  // Fix: Pass correct variable
        ]);
    }
    
}
