<?php

namespace App\Controller;

use App\Entity\FormPost;
use App\Form\FormPostType;
use App\Repository\FormPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/form/post')]
final class FormPostController extends AbstractController
{
    #[Route(name: 'app_form_post_index', methods: ['GET'])]
    public function index(FormPostRepository $formPostRepository): Response
    {
        return $this->render('form_post/index.html.twig', [
            'form_posts' => $formPostRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_form_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $formPost = new FormPost();
        $form = $this->createForm(FormPostType::class, $formPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($formPost);
            $entityManager->flush();

            return $this->redirectToRoute('app_form_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('form_post/new.html.twig', [
            'form_post' => $formPost,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_form_post_show', methods: ['GET'])]
    public function show(FormPost $formPost): Response
    {
        return $this->render('form_post/show.html.twig', [
            'form_post' => $formPost,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_form_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FormPost $formPost, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FormPostType::class, $formPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_form_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('form_post/edit.html.twig', [
            'form_post' => $formPost,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_form_post_delete', methods: ['POST'])]
    public function delete(Request $request, FormPost $formPost, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$formPost->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($formPost);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_form_post_index', [], Response::HTTP_SEE_OTHER);
    }
}
