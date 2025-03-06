<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Orders;
use App\Entity\Product;
use App\Repository\OrdersRepository;
use App\Repository\ProductRepository;
use App\Repository\FicheRepository;

use App\Form\OrderType;
use App\Repository\PrescriptionRepository;
use App\Repository\TraitementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ConsultationRepository;

#[Route('/admin')]
class AdminProductController extends AbstractController
{
    #[Route('/', name: 'adminn_dashboard')]
    public function index(FicheRepository $ficheRepository,ConsultationRepository $consultationRepository,ProductRepository $productRepository, OrdersRepository $orderRepository,
    PrescriptionRepository $prescriptionRepo,
            TraitementRepository $traitementRepo,
            EntityManagerInterface $entityManager
            ): Response
    {
        
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
        
        // Récupérer tous les produits
        $products = $productRepository->findAll();

        // Récupérer toutes les commandes
        $orders = $orderRepository->findAll();

        /// Get frequent patients
        $frequentPatients = $consultationRepository->findMostFrequentPatients(2);

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
        // Passer les données à la vue Twig
        return $this->render('product/admin/dashboard.html.twig', [
            'products' => $products,
            'orders' => $orders,
            'stats' => $stats,
            'frequent_patients' => $frequentPatients,
            'consultations' => $consultations,
            'fiches' => $fiches,
             'totalPrescriptions' => $totalPrescriptions,
            'totalTreatments' => $totalTreatments,
            'dosageLabels' => json_encode(array_keys($dosageRanges)),
            'dosageCounts' => json_encode(array_values($dosageRanges)),
            'medicationLabels' => json_encode($medicationLabels), // Fix: Pass correct variable
            'medicationCounts' => json_encode($medicationCounts)  // Fix: Pass correct variable
        ]);
    }


    #[Route('/admin/orders', name: 'app_order_indexx')]
    public function indexx(OrdersRepository $ordersRepository): Response
    {
        // Récupérer toutes les commandes
        $orders = $ordersRepository->findAll();

        return $this->render('product/order/index.html.twig', [
            'orders' => $orders,
        ]);
    }
    #[Route('/admin/orders/{id}', name: 'app_order_delete', methods: ['POST'])]
public function delete(Request $request, Orders $order, OrdersRepository $ordersRepository): Response
{
    // Vérifier le token CSRF pour la sécurité
    if ($this->isCsrfTokenValid('delete' . $order->getId(), $request->request->get('_token'))) {
        // Supprimer la commande
        $ordersRepository->remove($order, true);

        // Message de succès
        $this->addFlash('success', 'Order deleted successfully.');
    } else {
        // Message d'erreur si le token CSRF est invalide
        $this->addFlash('error', 'Invalid CSRF token.');
    }

    // Rediriger vers la liste des commandes
    return $this->redirectToRoute('app_order_indexx', [], Response::HTTP_SEE_OTHER);
}

#[Route('/admin/orders/{id}/edit', name: 'app_order_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Orders $order, OrdersRepository $ordersRepository): Response
{
    // Créer un formulaire pour éditer la commande
    $form = $this->createForm(OrderType::class, $order);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Enregistrer les modifications
        $ordersRepository->save($order, true);

        // Message de succès
        $this->addFlash('success', 'Order updated successfully.');

        // Rediriger vers la liste des commandes
        return $this->redirectToRoute('app_order_indexx', [], Response::HTTP_SEE_OTHER);
    }

    // Afficher le formulaire d'édition
    return $this->render('product/order/edit.html.twig', [
        'order' => $order,
        'form' => $form->createView(),
    ]);
}

#[Route('/admin/orders/{id}', name: 'app_order_show', methods: ['GET'])]
public function show(Orders $order): Response
{
    // Afficher les détails de la commande
    return $this->render('product/order/show.html.twig', [
        'order' => $order,
    ]);
}
}
