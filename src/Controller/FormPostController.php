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
use App\Repository\ReponseRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/form/post')]
final class FormPostController extends AbstractController
{
    #[Route('/front', name: 'app_form_post_index', methods: ['GET'])]
    public function index(FormPostRepository $formPostRepository, ReponseRepository $reponseRepository): Response
    {
        return $this->render('form_post/index.html.twig', [
            'form_posts' => $formPostRepository->findAll(),
            'reponses' => $reponseRepository->findAll(),
        ]);
    }

    #[Route('/back', name: 'app_form_back_index', methods: ['GET'])]
    public function backIndex(FormPostRepository $formPostRepository, ReponseRepository $reponseRepository): Response
    {
        return $this->render('form_back/index.html.twig', [
            'form_posts' => $formPostRepository->findAll(),
            'reponses' => $reponseRepository->findAll(),
        ]);
    }

    #[Route('/front/new', name: 'app_form_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $formPost = new FormPost();
        $form = $this->createForm(FormPostType::class, $formPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($formPost);
            $entityManager->flush();

            return $this->redirectToRoute('app_form_post_index');
        }

        return $this->render('form_post/new.html.twig', [
            'form_post' => $formPost,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/back/new', name: 'app_form_back_new', methods: ['GET', 'POST'])]
    public function backNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $formPost = new FormPost();
        $form = $this->createForm(FormPostType::class, $formPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($formPost);
            $entityManager->flush();

            return $this->redirectToRoute('app_form_back_index');
        }

        return $this->render('form_back/new.html.twig', [
            'form_post' => $formPost,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/front/{id}/edit', name: 'app_form_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FormPost $formPost, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FormPostType::class, $formPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_form_post_index');
        }

        return $this->render('form_post/edit.html.twig', [
            'form_post' => $formPost,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/back/{id}/edit', name: 'app_form_back_edit', methods: ['GET', 'POST'])]
    public function backEdit(Request $request, FormPost $formPost, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FormPostType::class, $formPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_form_back_index');
        }

        return $this->render('form_back/edit.html.twig', [
            'form_post' => $formPost,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/front/{id}/delete', name: 'app_form_post_delete', methods: ['POST'])]
    public function delete(Request $request, FormPost $formPost, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($this->isCsrfTokenValid('delete' . $formPost->getId(), $request->request->get('_token'))) {
            $entityManager->remove($formPost);
            $entityManager->flush();
            
            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['success' => false], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/back/{id}/delete', name: 'app_form_back_delete', methods: ['POST'])]
    public function backDelete(Request $request, FormPost $formPost, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($this->isCsrfTokenValid('delete' . $formPost->getId(), $request->request->get('_token'))) {
            $entityManager->remove($formPost);
            $entityManager->flush();
            
            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['success' => false], Response::HTTP_BAD_REQUEST);
    }
}
