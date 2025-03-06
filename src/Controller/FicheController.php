<?php

namespace App\Controller;

use App\Entity\Fiche;
use App\Entity\Review;
use App\Entity\Message;
use App\Form\FicheType;
use App\Form\ReviewType;
use App\Repository\FicheRepository;
use App\Service\PdfService;
use App\Service\TranslationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/fiche')]
class FicheController extends AbstractController
{
    #[Route('/', name: 'app_fiche_index', methods: ['GET'])]
    public function index(FicheRepository $ficheRepository): Response
    {
        return $this->render('consultation/fiche/index.html.twig', [
            'fiches' => $ficheRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_fiche_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $fiche = new Fiche();
        $fiche->setMotif('');
        $fiche->setDiagnostic('');
        $fiche->setTraitement('');
        $fiche->setDate(new \DateTime());
        $fiche->setStatus('pending');
        
        $form = $this->createForm(FicheType::class, $fiche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($fiche);
            $entityManager->flush();

            $this->addFlash('success', 'La fiche a été créée avec succès.');
            return $this->redirectToRoute('app_fiche_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('consultation/fiche/new.html.twig', [
            'fiche' => $fiche,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_fiche_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Fiche $fiche, EntityManagerInterface $entityManager): Response
    {
        // Handle review submission
        $review = new Review();
        $review->setFiche($fiche);
        $review->setPatientName($fiche->getNomPatient());
        $review->setDoctorName($fiche->getNomMedecin());
        
        $reviewForm = $this->createForm(ReviewType::class, $review);
        $reviewForm->handleRequest($request);

        if ($reviewForm->isSubmitted() && $reviewForm->isValid()) {
            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('success', 'Merci pour votre avis !');
            return $this->redirectToRoute('app_fiche_show', ['id' => $fiche->getId()]);
        }

        // Handle chat messages
        if ($request->isMethod('POST') && $request->request->has('message')) {
            $content = $request->request->get('message');
            $type = $request->request->get('type');

            if ($content && $type) {
                $message = new Message();
                $message->setContent($content);
                $message->setType($type);
                $message->setConsultation($fiche->getConsultation());
                
                $entityManager->persist($message);
                $entityManager->flush();

                return new JsonResponse([
                    'id' => $message->getId(),
                    'content' => $message->getContent(),
                    'type' => $message->getType(),
                    'createdAt' => $message->getCreatedAt()->format('d/m/Y H:i'),
                ]);
            }

            return new JsonResponse(['error' => 'Message content and type are required'], 400);
        }

        return $this->render('consultation/fiche/show.html.twig', [
            'fiche' => $fiche,
            'reviewForm' => $reviewForm->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_fiche_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Fiche $fiche, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FicheType::class, $fiche);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La fiche a été modifiée avec succès.');
            return $this->redirectToRoute('app_fiche_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('consultation/fiche/edit.html.twig', [
            'fiche' => $fiche,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_fiche_delete', methods: ['POST'])]
    public function delete(Request $request, Fiche $fiche, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$fiche->getId(), $request->request->get('_token'))) {
            $entityManager->remove($fiche);
            $entityManager->flush();
            $this->addFlash('success', 'La fiche a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_fiche_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/consultation/{id}', name: 'app_fiche_by_consultation', methods: ['GET'])]
    public function byConsultation(int $id, FicheRepository $ficheRepository): Response
    {
        return $this->render('consultation/fiche/by_consultation.html.twig', [
            'fiches' => $ficheRepository->findByConsultation($id),
        ]);
    }

    #[Route('/{id}/pdf', name: 'app_fiche_pdf', methods: ['GET'])]
    public function generatePdf(Fiche $fiche, PdfService $pdfService): Response
    {
        $html = $this->renderView('consultation/fiche/pdf.html.twig', [
            'fiche' => $fiche,
        ]);

        return new Response(
            $pdfService->generateBinaryPDF($html),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="fiche-'.$fiche->getId().'.pdf"'
            ]
        );
    }

    #[Route('/{id}/mark-notifications-read', name: 'app_fiche_mark_notifications_read', methods: ['POST'])]
    public function markNotificationsAsRead(Fiche $fiche, EntityManagerInterface $entityManager): JsonResponse
    {
        foreach ($fiche->getDateChangeNotifications() as $notification) {
            if (!$notification->isRead()) {
                $notification->setIsRead(true);
            }
        }
        
        $entityManager->flush();
        
        return new JsonResponse(['success' => true]);
    }

    #[Route('/{id}/translate', name: 'app_fiche_translate', methods: ['POST'])]
    public function translate(Request $request, Fiche $fiche, TranslationService $translationService): JsonResponse
    {
        $targetLang = $request->request->get('targetLang', 'en');
        
        $translatedFiche = [
            'motif' => $translationService->translate($fiche->getMotif() ?? '', $targetLang),
            'diagnostic' => $translationService->translate($fiche->getDiagnostic() ?? '', $targetLang),
            'traitement' => $translationService->translate($fiche->getTraitement() ?? '', $targetLang),
        ];

        return new JsonResponse($translatedFiche);
    }
}
