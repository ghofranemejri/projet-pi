<?php

namespace App\Controller;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Label;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class QrCodeController extends AbstractController
{
    public function __construct(private BuilderInterface $qrCodeBuilder) {}

    #[Route('/qr-code', name: 'generate_qr_code')]
    public function generateQrCode(): Response
    {
        // Créer un QR code pointant vers la liste des disponibilités
        $url = $this->generateUrl('app_disponibilite_index', [], true);

        $result = $this->qrCodeBuilder
            ->size(250) // Taille du QR Code
            ->margin(10) // Marge autour du QR Code
            ->data($url) // URL encodée dans le QR Code
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh()) // Niveau de correction d'erreur
            ->label(new Label('Scanner pour voir les disponibilités'))
            ->build();

        return new Response($result->getString(), Response::HTTP_OK, ['Content-Type' => $result->getMimeType()]);
    }

    #[Route('/show-qr-code', name: 'show_qr_code')]
    public function showQrCode(): Response
    {
        return $this->render('qr_code/show.html.twig');
    }
}
