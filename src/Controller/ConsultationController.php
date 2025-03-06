<?php

namespace App\Controller;

use App\Entity\Consultation;
use App\Entity\DateChangeNotification;
use App\Entity\Message;
use App\Form\ConsultationType;
use App\Repository\ConsultationRepository;
use App\Service\SmsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/consultation')]
class ConsultationController extends AbstractController
{
    #[Route('/', name: 'app_consultation_index', methods: ['GET'])]
    public function index(ConsultationRepository $consultationRepository): Response
    {
        return $this->render('consultation/consultation/index.html.twig', [
            'consultations' => $consultationRepository->findAll(),
        ]);
    }

    #[Route('/filter', name: 'app_consultation_filter', methods: ['GET'])]
    public function filter(Request $request, ConsultationRepository $consultationRepository): JsonResponse
    {
        $search = $request->query->get('search');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        $consultations = $consultationRepository->findByFilters($search, $startDate, $endDate);

        $result = [];
        foreach ($consultations as $consultation) {
            $result[] = [
                'id' => $consultation->getId(),
                'nomMedecin' => $consultation->getNomMedecin(),
                'nomPatient' => $consultation->getNomPatient(),
                'dateConsultation' => $consultation->getDateConsultation()->format('Y-m-d H:i'),
                'showUrl' => $this->generateUrl('app_consultation_show', ['id' => $consultation->getId()]),
                'editUrl' => $this->generateUrl('app_consultation_edit', ['id' => $consultation->getId()]),
                'deleteUrl' => $this->generateUrl('app_consultation_delete', ['id' => $consultation->getId()]),
                'deleteToken' => $this->renderView('consultation/_delete_token.html.twig', ['consultation' => $consultation])
            ];
        }

        return new JsonResponse($result);
    }

    #[Route('/new', name: 'app_consultation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $consultation = new Consultation();
        $form = $this->createForm(ConsultationType::class, $consultation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($consultation);
            $entityManager->flush();

            $this->addFlash('success', 'La consultation a été créée avec succès.');
            return $this->redirectToRoute('app_consultation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('consultation/consultation/new.html.twig', [
            'consultation' => $consultation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_consultation_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Consultation $consultation, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $content = $request->request->get('message');
            $type = $request->request->get('type');

            if ($content && $type) {
                $message = new Message();
                $message->setContent($content);
                $message->setType($type);
                $message->setConsultation($consultation);
                
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

        return $this->render('consultation/consultation/show.html.twig', [
            'consultation' => $consultation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_consultation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Consultation $consultation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ConsultationType::class, $consultation);
        $oldDate = clone $consultation->getDateConsultation();
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if date has changed
            if ($oldDate != $consultation->getDateConsultation()) {
                foreach ($consultation->getFiches() as $fiche) {
                    $notification = new DateChangeNotification();
                    $notification->setFiche($fiche)
                        ->setOldDate($oldDate)
                        ->setNewDate($consultation->getDateConsultation());
                    
                    $entityManager->persist($notification);
                }
            }
            
            $entityManager->flush();
            $this->addFlash('success', 'La consultation a été modifiée avec succès.');

            return $this->redirectToRoute('app_consultation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('consultation/consultation/edit.html.twig', [
            'consultation' => $consultation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_consultation_delete', methods: ['POST'])]
    public function delete(Request $request, Consultation $consultation, EntityManagerInterface $entityManager, SmsService $smsService): Response
    {
        if ($this->isCsrfTokenValid('delete'.$consultation->getId(), $request->request->get('_token'))) {
            try {
                // Send SMS notification before deleting
                $smsSent = $smsService->sendConsultationCancelledSms(
                    $consultation->getNomPatient(),
                    $consultation->getDateConsultation()
                );

                if (!$smsSent) {
                    $this->addFlash('error', 'Erreur lors de l\'envoi du SMS. Vérifiez les logs pour plus de détails.');
                    return $this->redirectToRoute('app_consultation_index');
                }

                $entityManager->remove($consultation);
                $entityManager->flush();

                $this->addFlash('success', 'La consultation a été supprimée et une notification SMS a été envoyée.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue: ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_consultation_index', [], Response::HTTP_SEE_OTHER);
    }
}
