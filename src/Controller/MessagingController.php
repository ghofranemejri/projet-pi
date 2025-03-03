<?php

namespace App\Controller;

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
use App\Entity\User;

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
    public function sendMessage(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['sender_id'], $data['content']) || empty(trim($data['content']))) {
            return new JsonResponse(['error' => 'Invalid input data'], 400);
        }

        $sender = $em->getRepository(User::class)->find($data['sender_id']);
        $receiver = $em->getRepository(User::class)->find($data['sender_id'] == 1 ? 2 : 1);

        if (!$sender || !$receiver) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $message = new Message();
        $message->setSender($sender);
        $message->setReceiver($receiver);
        $message->setContent($data['content']);
        $message->setIsRead(false);
        $em->persist($message);
        $em->flush();

        return new JsonResponse(['success' => true]);
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
            'sender_name' => $m->getSender()->getNom(),
        ], $messages);

        return new JsonResponse(['messages' => $messagesData]);
    }
}
