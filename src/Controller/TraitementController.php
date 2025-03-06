<?php

namespace App\Controller;

use App\Entity\Traitement;
use App\Form\TraitementType;
use App\Repository\TraitementRepository;
use App\Repository\PrescriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class TraitementController extends AbstractController
{
    private const MEDECIN_ID = 1; // ID statique du médecin connecté

    private $traitementRepository;
    private $prescriptionRepository;
    private $entityManager;

    public function __construct(TraitementRepository $traitementRepository, PrescriptionRepository $prescriptionRepository, EntityManagerInterface $entityManager)
    {
        $this->traitementRepository = $traitementRepository;
        $this->prescriptionRepository = $prescriptionRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/traitement', name: 'traitement_index', methods: ['GET'])]
public function index(): Response
{
    $prescriptions = $this->prescriptionRepository->findAll();

   
    $traitements = $this->traitementRepository->findBy([
        'prescription' => $prescriptions
    ]);

    return $this->render('traitement/traitement/index.html.twig', [
        'traitements' => $traitements,
    ]);
}

    #[Route('/traitement/new/{id}', name: 'traitement_create')]
    public function create(int $id, Request $request): Response
    {
        $prescription = $this->prescriptionRepository->find($id);
        if (!$prescription) {
            throw $this->createNotFoundException('Prescription non trouvée');
        }

        $traitement = new Traitement();
        $traitement->setPrescription($prescription);

        $form = $this->createForm(TraitementType::class, $traitement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->persist($traitement);
            $this->entityManager->flush();

            $this->addFlash('success', 'Traitement créé avec succès');
            return $this->redirectToRoute('prescription_index');
        }

        return $this->render('traitement/traitement/create.html.twig', [
            'form' => $form->createView(),
            'prescription' => $prescription
        ]);
    }


    #[Route('/traitement/{id}/edit', name: 'traitement_edit')]
    public function edit(int $id, Request $request): Response
    {
        $traitement = $this->traitementRepository->find($id);
        if (!$traitement) {
            throw $this->createNotFoundException('Traitement non trouvé');
        }

        $form = $this->createForm(TraitementType::class, $traitement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Traitement mis à jour avec succès');
            return $this->redirectToRoute('prescription_index');
        }

        return $this->render('traitement/traitement/edit.html.twig', [
            'form' => $form->createView(),
            'traitement' => $traitement
        ]);
    }

    #[Route('/traitement/{id}/delete', name: 'traitement_delete')]
    public function delete(int $id): Response
    {
        $traitement = $this->traitementRepository->find($id);
        if (!$traitement) {
            throw $this->createNotFoundException('Traitement non trouvé');
        }

        $this->entityManager->remove($traitement);
        $this->entityManager->flush();

        $this->addFlash('success', 'Traitement supprimé avec succès');
        return $this->redirectToRoute('prescription_index');
    }
}
