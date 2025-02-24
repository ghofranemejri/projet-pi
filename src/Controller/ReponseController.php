<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Form\ReponseType;
use App\Repository\ReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reponse')]
final class ReponseController extends AbstractController
{
    #[Route(name: 'app_reponse_index', methods: ['GET'])]
    public function index(ReponseRepository $reponseRepository): Response
    {
        return $this->render('reponse/index.html.twig', [
            'reponses' => $reponseRepository->findAll(),
        ]);
    }
    




    #[Route('/new', name: 'app_reponse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reponse);
            $entityManager->flush();

            return $this->redirectToRoute('app_form_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse/new.html.twig', [
            'reponse' => $reponse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reponse_show', methods: ['GET'])]
    public function show(Reponse $reponse): Response
    {
        return $this->render('reponse/show.html.twig', [
            'reponse' => $reponse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reponse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reponse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse/edit.html.twig', [
            'reponse' => $reponse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reponse_delete', methods: ['POST'])]
    public function delete(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reponse->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reponse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reponse_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/back',name: 'app_reponse_back_index', methods: ['GET'])]
    public function backindex(ReponseRepository $reponseRepository): Response
    {
        return $this->render('reponse_back/index.html.twig', [
            'reponses' => $reponseRepository->findAll(),
        ]);
    }

#[Route('/reponse/new', name: 'app_reponse_back_new', methods: ['POST'])]
public function newReponse(Request $request, EntityManagerInterface $entityManager, FormPostRepository $formPostRepository): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    if (!$data || !isset($data['contenu']) || !isset($data['formPostId']) || !isset($data['_token'])) {
        return new JsonResponse(['success' => false, 'message' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
    }

    $csrfToken = $data['_token'];
    if (!$this->isCsrfTokenValid('add_comment' . $data['formPostId'], $csrfToken)) {
        return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide'], Response::HTTP_FORBIDDEN);
    }

    $formPost = $formPostRepository->find($data['formPostId']);
    if (!$formPost) {
        return new JsonResponse(['success' => false, 'message' => 'Post non trouvé'], Response::HTTP_NOT_FOUND);
    }

    $reponse = new Reponse();
    $reponse->setContenu($data['contenu']);
    $reponse->setFormPost($formPost);
    
    $entityManager->persist($reponse);
    $entityManager->flush();

    return new JsonResponse(['success' => true, 'message' => 'Commentaire ajouté avec succès']);
}

#[Route('/back/new', name: 'app_reponse_back_new', methods: ['GET', 'POST'])]
public function newback(Request $request, EntityManagerInterface $entityManager): Response
{
    $reponse = new Reponse();
    $form = $this->createForm(ReponseType::class, $reponse);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($reponse);
        $entityManager->flush();

        return $this->redirectToRoute('app_form_back_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('reponse_back/new.html.twig', [
        'reponse' => $reponse,
        'form' => $form,
    ]);
}

    #[Route('/{id}', name: 'app_reponse_back_show', methods: ['GET'])]
    public function showBack(Reponse $reponse): Response
    {
        return $this->render('reponse_back/show.html.twig', [
            'reponse_back' => $reponse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reponse_back_edit', methods: ['GET', 'POST'])]
    public function editBack(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reponse_back_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse_back/edit.html.twig', [
            'reponse_back' => $reponse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reponse_back_delete', methods: ['POST'])]
    public function deleteBack(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reponse->getId(), $request->get('_token'))) {
            $entityManager->remove($reponse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reponse_back_index', [], Response::HTTP_SEE_OTHER);
    }
}
