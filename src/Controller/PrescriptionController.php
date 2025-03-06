<?php

namespace App\Controller;

use App\Entity\Prescription;
use App\Entity\User;

use App\Form\PrescriptionType;
use App\Repository\PrescriptionRepository;
use App\Repository\TraitementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\NotificationRepository;

use App\Entity\Notification;

#[Route('/prescription')]
class PrescriptionController extends AbstractController
{
    private const MEDECIN_ID = 1;
    private const PATIENT_ID = 2;
    private PrescriptionRepository $prescriptionRepository;
    
   
    public function __construct(PrescriptionRepository $prescriptionRepository)
    {
        $this->prescriptionRepository = $prescriptionRepository;
    }

    #[Route('/prescription_index', name: 'prescription_index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        // Corrected reference to the repository
        $query = $this->prescriptionRepository->createQueryBuilder('p')
            ->orderBy('p.datedeb', 'DESC')
            ->getQuery();

        $prescriptions = $paginator->paginate(
            $query, 
            $request->query->getInt('page', 1), 
            5 
        );

        return $this->render('traitement/prescription/index.html.twig', [
            'prescriptions' => $prescriptions,
        ]);
    }

    #[Route('/newprescription_new', name: 'prescription_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $prescription = new Prescription();
        $prescription->setCreatedAt(new \DateTime());
        $prescription->setDatedeb(new \DateTime());
        $dateFin = new \DateTime(); // Crée l'objet DateTime
        $dateFin->modify('-1 day'); // Modifie la date (ajoute ou soustrait des jours)
        
        $prescription->setDatefin($dateFin); // Assigne la date modifiée à la prescription
        $prescription->setMedecin($entityManager->getRepository(User::class)->find($this->getUser()->getId()));

        $form = $this->createForm(PrescriptionType::class, $prescription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($prescription);
            $entityManager->flush();

            return $this->redirectToRoute('prescription_index');
        }

        return $this->render('traitement/prescription/new.html.twig', [
            'prescription' => $prescription,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'prescription_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Prescription $prescription, EntityManagerInterface $entityManager, NotificationRepository $notificationRepository): Response
    {
        // Vérifier si le médecin est bien propriétaire de la prescription
        if ($prescription->getMedecin()->getId() !== self::MEDECIN_ID) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres prescriptions.');
        }
    
        $form = $this->createForm(PrescriptionType::class, $prescription);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
    
            // ✅ Create a notification for the patient
            $notification = new Notification();
            $notification->setUser($prescription->getPatient()); // Send notification to the patient
            $notification->setMessage("Votre prescription a été modifiée.");
            $notification->setIsRead(false);
           
    
            $entityManager->persist($notification);
            $entityManager->flush();
    
            return $this->redirectToRoute('prescription_index');
        }
    
        return $this->render('traitement/prescription/edit.html.twig', [
            'prescription' => $prescription,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id}', name: 'prescription_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer la prescription par son ID
        $prescription = $entityManager->getRepository(Prescription::class)->find($id);
    
        // Vérifier si la prescription existe
        if (!$prescription) {
            throw $this->createNotFoundException('La prescription demandée n\'existe pas.');
        }
    
      
    
        // Vérifier la validité du token CSRF
        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $entityManager->remove($prescription);
            $entityManager->flush();
        }
    
        return $this->redirectToRoute('prescription_index');
    }
    
    

    #[Route('/patient', name: 'patient_prescriptions')]
    public function showPrescriptions(
        PrescriptionRepository $prescriptionRepository,
        TraitementRepository $traitementRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $prescriptions = $prescriptionRepository->findBy(getUser()->getId());
    
        $traitements = [];
        foreach ($prescriptions as $prescription) {
            $traitements[$prescription->getId()] = $traitementRepository->findBy(['prescription' => $prescription]);
    
            // Create a notification for the prescription user
            $notification = new Notification();
            $notification->setUser($prescription->getPatient());
            $notification->setMessage("Your prescription has been viewed by an admin.");
            $entityManager->persist($notification);
        }
    
        $entityManager->flush(); // Save notifications to the database
    
        return $this->render('traitement/prescription/show.html.twig', [
            'prescriptions' => $prescriptions,
            'traitements' => $traitements,
        ]);
    }

    #[Route('/api/prescriptions', name: 'api_prescriptions', methods: ['GET'])]
    public function getPrescriptions(PrescriptionRepository $prescriptionRepository): JsonResponse
    {
        // Static User ID (Change this to simulate different users)
        $staticUserId = 2; // Change to 2 if needed
    
        // Fetch prescriptions where the patient ID matches the static user ID
        $prescriptions = $prescriptionRepository->findBy(['patient' => $staticUserId]);
    
        $events = [];
    
        foreach ($prescriptions as $prescription) {
            $events[] = [
                'id' => $prescription->getId(),
                'title' => 'Prescription for Patient ' . $prescription->getPatient()->getId(),
                'start' => $prescription->getDatedeb()->format('Y-m-d\TH:i:s'),
                'end' => $prescription->getDatefin()->format('Y-m-d\TH:i:s'),
                'address' => $prescription->getAddress(),
            ];
        }
    
        return new JsonResponse($events);
    }
    
    #[Route('/calendar', name: 'prescription_calendar')]
    public function calendar(): Response
    {
        // Static user for now
        $staticUserId = 2; // Change to 2 if testing another user
    
        return $this->render('traitement/prescription/calendar.html.twig', [
            'userId' => $staticUserId,
        ]);
    }
    public function getUserNotifications(NotificationRepository $notificationRepository): array
{
    if (!$this->getUser()) {
        return [];
    }

    return $notificationRepository->findBy(['user' => $this->getUser(), 'isRead' => false]);
}
#[Route('/notifications/mark-read', name: 'mark_notifications_read', methods: ['POST'])]
public function markNotificationsRead(EntityManagerInterface $entityManager, NotificationRepository $notificationRepository): JsonResponse
{
    // 🔹 Manually set a static user (Change to ID 1 or 2 based on testing)
    $staticUserId = 1; // Change this to 2 if testing for another user

    // Find notifications for the static user
    $notifications = $notificationRepository->findBy(['user' => $staticUserId, 'isRead' => false]);

    foreach ($notifications as $notification) {
        $notification->setIsRead(true);
    }

    $entityManager->flush();

    return new JsonResponse(['success' => true]);
}

}
