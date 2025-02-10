<?php

namespace App\Controller;

use App\Entity\NomDeProduit;
use App\Form\NomDeProduitType;
use App\Repository\NomDeProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nom/de/produit')]
final class NomDeProduitController extends AbstractController
{
    #[Route(name: 'app_nom_de_produit_index', methods: ['GET'])]
    public function index(NomDeProduitRepository $nomDeProduitRepository): Response
    {
        return $this->render('nom_de_produit/index.html.twig', [
            'nom_de_produits' => $nomDeProduitRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_nom_de_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $nomDeProduit = new NomDeProduit();
        $form = $this->createForm(NomDeProduitType::class, $nomDeProduit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($nomDeProduit);
            $entityManager->flush();

            return $this->redirectToRoute('app_nom_de_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('nom_de_produit/new.html.twig', [
            'nom_de_produit' => $nomDeProduit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_nom_de_produit_show', methods: ['GET'])]
    public function show(NomDeProduit $nomDeProduit): Response
    {
        return $this->render('nom_de_produit/show.html.twig', [
            'nom_de_produit' => $nomDeProduit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_nom_de_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, NomDeProduit $nomDeProduit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(NomDeProduitType::class, $nomDeProduit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_nom_de_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('nom_de_produit/edit.html.twig', [
            'nom_de_produit' => $nomDeProduit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_nom_de_produit_delete', methods: ['POST'])]
    public function delete(Request $request, NomDeProduit $nomDeProduit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$nomDeProduit->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($nomDeProduit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_nom_de_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}
