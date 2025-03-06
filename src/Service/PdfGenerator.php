<?php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfGenerator
{
    private $dompdf;

    public function __construct()
    {
        // Initialiser Dompdf avec des options
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $this->dompdf = new Dompdf($options);
    }

    public function generatePdf(string $htmlContent): string
    {
        // Charger le contenu HTML dans Dompdf
        $this->dompdf->loadHtml($htmlContent);

        // Définir le format de page (A4 en portrait)
        $this->dompdf->setPaper('A4', 'portrait');

        // Convertir le HTML en PDF
        $this->dompdf->render();

        // Retourner le PDF en tant que chaîne de caractères
        return $this->dompdf->output();
    }
}
