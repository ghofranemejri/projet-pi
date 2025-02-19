<?php

namespace App\Controller;

use App\Entity\Disponibilite;
use App\Form\Disponibilite1Type;
use App\Repository\DisponibiliteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/disponibilite')]
final class DisponibiliteController extends AbstractController
{
    #[Route(name: 'app_disponibilite_index', methods: ['GET'])]
    public function index(DisponibiliteRepository $disponibiliteRepository): Response
    {
        return $this->render('disponibilite/index.html.twig', [
            'disponibilites' => $disponibiliteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_disponibilite_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $disponibilite = new Disponibilite();
        $form = $this->createForm(Disponibilite1Type::class, $disponibilite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($disponibilite);
            $entityManager->flush();

            return $this->redirectToRoute('app_disponibilite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('disponibilite/new.html.twig', [
            'disponibilite' => $disponibilite,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_disponibilite_show', methods: ['GET'], requirements: ['id' => '\d+'])]
public function show(DisponibiliteRepository $repository, int $id): Response
{
    $disponibilite = $repository->find($id);
    if (!$disponibilite) {
        throw new NotFoundHttpException("Disponibilité avec l'ID $id introuvable.");
    }

    return $this->render('disponibilite/show.html.twig', [
        'disponibilite' => $disponibilite,
    ]);
}


    #[Route('/{id}/edit', name: 'app_disponibilite_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DisponibiliteRepository $repository, EntityManagerInterface $entityManager, int $id): Response
    {
        $disponibilite = $repository->find($id);
        if (!$disponibilite) {
            throw new NotFoundHttpException("Disponibilité avec l'ID $id introuvable.");
        }

        $form = $this->createForm(Disponibilite1Type::class, $disponibilite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_disponibilite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('disponibilite/edit.html.twig', [
            'disponibilite' => $disponibilite,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_disponibilite_delete', methods: ['POST'])]
    public function delete(Request $request, DisponibiliteRepository $repository, EntityManagerInterface $entityManager, int $id): Response
    {
        $disponibilite = $repository->find($id);
        if (!$disponibilite) {
            throw new NotFoundHttpException("Disponibilité avec l'ID $id introuvable.");
        }

        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $entityManager->remove($disponibilite);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_disponibilite_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/back', name: 'app_disponibilite_index_back', methods: ['GET'])]
    public function back_index(DisponibiliteRepository $repository): Response
    {
        return $this->render('disponibilite_back/index.html.twig', [
            'disponibilites' => $repository->findAll(),
        ]);
    }

    #[Route('/back/new', name: 'app_disponibilite_new_back', methods: ['GET', 'POST'])]
    public function backnew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $disponibilite = new Disponibilite();
        $form = $this->createForm(Disponibilite1Type::class, $disponibilite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($disponibilite);
            $entityManager->flush();

            return $this->redirectToRoute('app_disponibilite_index_back', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('disponibilite_back/new.html.twig', [
            'disponibilite' => $disponibilite,
            'form' => $form,
        ]);
    }

    #[Route('/back/{id}/show', name: 'app_disponibilite_show_back', methods: ['GET'])]
    public function backshow(DisponibiliteRepository $repository, int $id): Response
    {
        $disponibilite = $repository->find($id);
        if (!$disponibilite) {
            throw new NotFoundHttpException("Disponibilité avec l'ID $id introuvable.");
        }

        return $this->render('disponibilite_back/show.html.twig', [
            'disponibilite' => $disponibilite,
        ]);
    }

    #[Route('/back/{id}/edit', name: 'app_disponibilite_edit_back', methods: ['GET', 'POST'])]
    public function backedit(Request $request, DisponibiliteRepository $repository, EntityManagerInterface $entityManager, int $id): Response
    {
        $disponibilite = $repository->find($id);
        if (!$disponibilite) {
            throw new NotFoundHttpException("Disponibilité avec l'ID $id introuvable.");
        }

        $form = $this->createForm(Disponibilite1Type::class, $disponibilite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_disponibilite_index_back', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('disponibilite_back/edit.html.twig', [
            'disponibilite' => $disponibilite,
            'form' => $form,
        ]);
    }

    #[Route('/back/delete/{id}', name: 'app_disponibilite_delete_back', methods: ['POST'])]
    public function backdelete(Request $request, DisponibiliteRepository $repository, EntityManagerInterface $entityManager, int $id): Response
    {
        $disponibilite = $repository->find($id);
        if (!$disponibilite) {
            throw new NotFoundHttpException("Disponibilité avec l'ID $id introuvable.");
        }

        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $entityManager->remove($disponibilite);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_disponibilite_index_back', [], Response::HTTP_SEE_OTHER);
    }
}
