<?php
namespace App\EventListener;

use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

class NotificationListener
{
    private Environment $twig;
    private EntityManagerInterface $entityManager;
    private NotificationRepository $notificationRepository;

    public function __construct(Environment $twig, EntityManagerInterface $entityManager, NotificationRepository $notificationRepository)
    {
        $this->twig = $twig;
        $this->entityManager = $entityManager;
        $this->notificationRepository = $notificationRepository;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        // 🔹 Use a static user for now (replace with actual authentication later)
        $staticUserId = 1; // Change to 2 if testing for another user

        // Fetch notifications for this user
        $notifications = $this->notificationRepository->findBy(['user' => $staticUserId, 'isRead' => false]);

        // Pass notifications to Twig globally
        $this->twig->addGlobal('notifications', $notifications);
    }
}
