<?php

// src/Controller/ChatbotController.php
namespace App\Controller;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChatbotController extends AbstractController
{
    /**
     * @Route("/chatbot", name="chatbot")
     */
    public function index()
    {
        // Configuration de BotMan
        DriverManager::loadDriver(\BotMan\Driver\Web\WebDriver::class);
        $config = [];
        $botman = BotManFactory::create($config);

        $botman->hears('Salut', function (BotMan $bot) {
            $bot->reply('Salut ! Comment puis-je t’aider aujourd’hui ?');
        });

        $botman->hears('.*', function (BotMan $bot) {
            $bot->reply('Je n’ai pas compris cela, peux-tu reformuler ?');
        });

        // Demander à BotMan de traiter la requête HTTP
        $botman->listen();

        return new Response('Chatbot prêt !');
    }
}
