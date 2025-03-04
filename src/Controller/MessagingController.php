<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Message;
use App\Entity\Conversation;
use App\Repository\MessageRepository;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route('/messaging')]
class MessagingController extends AbstractController
{
    #[Route('/', name: 'messenger', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $user1 = $em->getRepository(User::class)->find(1);
        $user2 = $em->getRepository(User::class)->find(2);
        
        return $this->render('form_post/mess.html.twig', [
            'user1' => $user1,
            'user2' => $user2,
        ]);
    }

    #[Route('/send', name: 'send_message', methods: ['POST'])]
    public function sendMessage(
        Request $request,
        EntityManagerInterface $em,
        ConversationRepository $conversationRepo
    ): JsonResponse {
        // Utiliser les utilisateurs statiques ID 1 et ID 2
        $user1 = $em->getRepository(User::class)->find(2);
        $user2 = $em->getRepository(User::class)->find(1);

        if (!$user1 || !$user2) {
            return new JsonResponse(['error' => 'One or both static users not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['content']) || empty(trim($data['content']))) {
            return new JsonResponse(['error' => 'Invalid input data'], 400);
        }

        // Déterminer l'expéditeur et le destinataire de manière alternée
        $sender = (rand(0, 1) === 0) ? $user1 : $user2;
        $receiver = ($sender === $user1) ? $user2 : $user1;

        // Trouver ou créer une conversation
        $conversation = $conversationRepo->findOneBy([
            'user1' => $user1,
            'user2' => $user2
        ]) ?? $conversationRepo->findOneBy([
            'user1' => $user2,
            'user2' => $user1
        ]);

        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->setUser1($user1);
            $conversation->setUser2($user2);
            $em->persist($conversation);
        }

        // Créer un nouveau message
        $message = new Message();
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
    public function fetchMessages(MessageRepository $messageRepo): JsonResponse
    {
        $messages = $messageRepo->createQueryBuilder('m')
            ->where('m.sender IN (1,2) AND m.receiver IN (1,2)')
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        $messagesData = array_map(fn($m) => [
            'id' => $m->getId(),
            'content' => $m->getContent(),
            'created_at' => $m->getCreatedAt()->format('H:i'),
            'sender_id' => $m->getSender()->getId(),
            'sender_name' => $m->getSender()->getName(),
        ], $messages);

        return new JsonResponse(['messages' => $messagesData]);
    }
}
