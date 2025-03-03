<?php

// src/Controller/PdfController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\GotenbergService;

class PdfController extends AbstractController
{
    private GotenbergService $gotenbergService;

    public function __construct(GotenbergService $gotenbergService)
    {
        $this->gotenbergService = $gotenbergService;
    }

    /**
     * @Route("/generate-pdf", name="generate_pdf")
     */
    public function generatePdf(Request $request): Response
    {
        $htmlFilePath = '/var/www/wr602d-mmi22g02/application/public/index.html'; // Mettez à jour ce chemin si nécessaire

        try {
            return $this->gotenbergService->generatePdfFromHtml($htmlFilePath);
        } catch (\RuntimeException $e) {
            return new Response($e->getMessage(), 500);
        }
    }
}