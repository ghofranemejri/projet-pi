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
use App\Entity\Reponse;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;


#[Route('/form/post')]
final class FormPostController extends AbstractController
{
    #[Route('/front', name: 'app_form_post_index', methods: ['GET'])]
    public function index(FormPostRepository $formPostRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $formPostRepository->findAll(); // Récupérer tous les posts
    
        $pagination = $paginator->paginate(
            $query, // Requête
            $request->query->getInt('page', 1), // Page actuelle (par défaut : 1)
            5 // Nombre d'éléments par page
        );
    
        return $this->render('form_post/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/back', name: 'app_form_back_index', methods: ['GET'])]
    public function backindex(FormPostRepository $formPostRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $formPostRepository->findAll(); // Récupérer tous les posts
    
        $pagination = $paginator->paginate(
            $query, // Requête
            $request->query->getInt('page', 1), // Page actuelle (par défaut : 1)
            5 // Nombre d'éléments par page
        );
    
        return $this->render('form_back/index.html.twig', [
            'pagination' => $pagination,
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
    #[Route('/form_post/{id}/comment', name: 'app_form_post_comment', methods: ['POST'])]
public function addComment(int $id, Request $request, EntityManagerInterface $entityManager, FormPostRepository $formPostRepository): JsonResponse
{
    // Vérifier si le post existe
    $formPost = $formPostRepository->find($id);
    if (!$formPost) {
        return new JsonResponse(['success' => false, 'message' => 'Post introuvable.'], 404);
    }

    // Récupérer les données de la requête
    $data = json_decode($request->getContent(), true);
    if (!$data || !isset($data['contenu']) || empty(trim($data['contenu']))) {
        return new JsonResponse(['success' => false, 'message' => 'Commentaire vide.'], 400);
    }

    // Créer le commentaire
    $comment = new Reponse();
    $comment->setContenu($data['contenu']);
    $comment->setPost($formPost);
    $comment->setDate(new \DateTime());

    // Sauvegarde en base de données
    $entityManager->persist($comment);
    $entityManager->flush();

    return new JsonResponse(['success' => true, 'message' => 'Commentaire ajouté avec succès !']);
}


#[Route('/recherche', name: 'app_form_post_search', methods: ['GET'])]
public function search(Request $request, FormPostRepository $formPostRepository): Response
{
    $query = $request->query->get('q', '');

    $formPosts = $formPostRepository->createQueryBuilder('p')
        ->where('p.nom LIKE :query OR p.description LIKE :query')
        ->setParameter('query', '%' . $query . '%')
        ->getQuery()
        ->getResult();

    // Si c'est une requête AJAX, renvoyer uniquement les résultats
    if ($request->isXmlHttpRequest()) {
        return $this->render('form_post/index.html.twig', [
            'form_posts' => $formPosts,
        ]);
    }

    return $this->render('form_post/search.html.twig', [
        'form_posts' => $formPosts,
        'query' => $query,
    ]);
}


#[Route('/admin/form/search', name: 'app_form_back_search', methods: ['GET'])]
public function searchback(Request $request, FormPostRepository $formPostRepository): JsonResponse
{
    $search = $request->query->get('search', '');
    $sort = $request->query->get('sort', '');

    $queryBuilder = $formPostRepository->createQueryBuilder('fp');

    if ($search) {
        $queryBuilder
            ->andWhere('fp.nom LIKE :search OR fp.description LIKE :search')
            ->setParameter('search', '%' . $search . '%');
    }

    if ($sort === 'name') {
        $queryBuilder->orderBy('fp.nom', 'ASC');
    } elseif ($sort === 'date') {
        $queryBuilder->orderBy('fp.date', 'DESC');
    } elseif ($sort === 'comments') {
        $queryBuilder->leftJoin('fp.reponses', 'r')
                     ->groupBy('fp.id')
                     ->orderBy('COUNT(r.id)', 'DESC');
    }

    $formPosts = $queryBuilder->getQuery()->getResult();

    $data = [];
    foreach ($formPosts as $post) {
        $data[] = [
            'id' => $post->getId(),
            'nom' => $post->getNom(),
            'date' => $post->getDate() ? $post->getDate()->format('Y-m-d H:i:s') : null,
            'description' => $post->getDescription(),
            'reponses' => array_map(fn($r) => ['contenu' => $r->getContenu()], $post->getReponses()->toArray()),
        ];
    }

    return new JsonResponse($data);
}
#[Route('/{id}/react', name: 'app_form_post_react', methods: ['POST'])]
public function likeDislike(FormPost $formPost, EntityManagerInterface $entityManager, Request $request): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $reaction = $data['reaction'] ?? null;

    if ($reaction === 'like') {
        $formPost->addLike();
    } elseif ($reaction === 'dislike') {
        $formPost->addDislike();
    }

    $entityManager->flush();

    return new JsonResponse([
        'likes' => $formPost->getLikes(),
        'dislikes' => $formPost->getDislikes(),
    ]);
}

    
}

