<?php


// src/Service/GotenbergService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;

class GotenbergService
{
    private HttpClientInterface $client;
    private string $gotenbergUrl;

    public function __construct(HttpClientInterface $client, string $gotenbergUrl)
    {
        $this->client = $client;
        $this->gotenbergUrl = $gotenbergUrl; // Injected Gotenberg URL
    }

    public function generatePdfFromHtml(string $htmlFilePath): Response
    {
        // Check if the HTML file exists
        if (!file_exists($htmlFilePath)) {
            throw new \RuntimeException('Le fichier HTML n\'existe pas : ' . $htmlFilePath);
        }

        // Prepare the data for the Gotenberg API
        $response = $this->client->request('POST', "{$this->gotenbergUrl}/forms/chromium/convert/html", [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
            ],
            'body' => [
                'files' => [
                    'index.html' => fopen($htmlFilePath, 'r'), // Ouvrir le fichier en lecture
                ],
            ],
        ]);

        // Check the response status
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Échec de la génération du PDF : ' . $response->getContent(false));
        }

        // Get the PDF content
        $pdfContent = $response->getContent();

        // Return the response with the PDF content
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="document.pdf"',
        ]);
    }
}