<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Messager;
use App\Entity\Conversation;
use App\Repository\UserRepository;
use App\Repository\MessagerRepository;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

#[Route('/messaging')]
class MessagingController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/', name: 'messenger', methods: ['GET'])]
    public function index(EntityManagerInterface $em, UserRepository $userRepo): Response
    {
        // Récupérer l'utilisateur connecté
        $currentUser = $this->security->getUser();

        // Récupérer l'administrateur
        $admin = $userRepo->findOneByRole('ROLE_ADMIN');

        if (!$admin) {
            throw $this->createNotFoundException('Admin not found.');
        }

        return $this->render('form/form_post/mess.html.twig', [
            'currentUser' => $currentUser,
            'admin' => $admin,
        ]);
    }

    #[Route('/send', name: 'send_message', methods: ['POST'])]
    public function sendMessage(
        Request $request,
        EntityManagerInterface $em,
        ConversationRepository $conversationRepo,
        UserRepository $userRepo
    ): JsonResponse {
        // Récupérer l'utilisateur connecté
        $currentUser = $this->security->getUser();

        // Récupérer l'administrateur
        $admin = $userRepo->findOneByRole('ROLE_ADMIN');

        if (!$currentUser || !$admin) {
            return new JsonResponse(['error' => 'User or admin not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['content']) || empty(trim($data['content']))) {
            return new JsonResponse(['error' => 'Invalid input data'], 400);
        }

        // Déterminer l'expéditeur et le destinataire
        $sender = $currentUser; // L'utilisateur connecté envoie le message
        $receiver = $admin; // L'administrateur reçoit le message

        // Trouver ou créer une conversation
        $conversation = $conversationRepo->findOneBy([
            'user1' => $currentUser,
            'user2' => $admin
        ]) ?? $conversationRepo->findOneBy([
            'user1' => $admin,
            'user2' => $currentUser
        ]);

        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->setUser1($currentUser);
            $conversation->setUser2($admin);
            $em->persist($conversation);
        }

        // Créer un nouveau message
        $message = new Messager();
        $message->setSender($sender);
        $message->setReceiver($receiver);
        $message->setConversation($conversation);
        $message->setContent($data['content']);
        $message->setIsRead(false);

        $em->persist($message);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Message sent successfully',
            'messageData' => [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'createdAt' => $message->getCreatedAt()->format('H:i'),
                'sender' => [
                    'id' => $message->getSender()->getId(),
                    'name' => $message->getSender()->getName()
                ],
                'receiver' => [
                    'id' => $message->getReceiver()->getId(),
                    'name' => $message->getReceiver()->getName()
                ]
            ]
        ]);
    }

    #[Route('/messages', name: 'fetch_messages', methods: ['GET'])]
    public function fetchMessages(
        MessagerRepository $messageRepo,
        UserRepository $userRepo
    ): JsonResponse {
        // Récupérer l'utilisateur connecté
        $currentUser = $this->security->getUser();

        // Récupérer l'administrateur
        $admin = $userRepo->findOneByRole('ROLE_ADMIN');

        if (!$currentUser || !$admin) {
            return new JsonResponse(['error' => 'User or admin not found.'], 404);
        }

        // Récupérer les messages entre l'utilisateur connecté et l'administrateur
        $messages = $messageRepo->createQueryBuilder('m')
            ->where('(m.sender = :currentUser AND m.receiver = :admin) OR (m.sender = :admin AND m.receiver = :currentUser)')
            ->setParameter('currentUser', $currentUser)
            ->setParameter('admin', $admin)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        $messagesData = array_map(fn($m) => [
            'id' => $m->getId(),
            'content' => $m->getContent(),
            'created_at' => $m->getCreatedAt()->format('H:i'),
            'sender_id' => $m->getSender()->getId(),
            'sender_name' => $m->getSender()->getEmail(),
        ], $messages);

        return new JsonResponse(['messages' => $messagesData]);
    }
}