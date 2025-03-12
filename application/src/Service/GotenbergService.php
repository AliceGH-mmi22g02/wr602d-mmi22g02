<?php

namespace App\Service;

use Exception;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;

class GotenbergService
{
    private HttpClientInterface $client;
    private string $gotenbergUrl;

    public function __construct(HttpClientInterface $client, ParameterBagInterface $params)
    {
        $this->client = $client;
        $this->gotenbergUrl = $params->get('gotenberg_url');
    }

    public function generatePdfFromHtml(string $htmlContent): Response
    {
        try {
            $boundary = '----WebKitFormBoundary' . bin2hex(random_bytes(16));
        } catch (Exception $e) {
            return new Response('Erreur lors de la génération du boundary : ' . $e->getMessage(), 500);
        }

        $body = "--$boundary\r\n";
        $body .= "Content-Disposition: form-data; name=\"files\"; filename=\"index.html\"\r\n";
        $body .= "Content-Type: text/html\r\n\r\n";
        $body .= $htmlContent . "\r\n";
        $body .= "--$boundary--\r\n";

        try {
            $response = $this->client->request(
                'POST',
                "$this->gotenbergUrl/forms/chromium/convert/html",
                [
                    'headers' => [
                        'Content-Type' => "multipart/form-data; boundary=$boundary",
                        'Accept' => 'application/pdf',
                    ],
                    'body' => $body,
                ]
            );
        } catch (TransportExceptionInterface $e) {
            return new Response('Erreur de transport : ' . $e->getMessage(), 500);
        }

        try {
            if ($response->getStatusCode() !== 200) {
                throw new RuntimeException('Erreur lors de la génération du PDF : ' . $response->getContent(false));
            }
        } catch (ClientExceptionInterface |
            RedirectionExceptionInterface |
            ServerExceptionInterface |
            TransportExceptionInterface $e
        ) {
            return new Response('Erreur lors de la requête au serveur : ' . $e->getMessage(), 500);
        }

        try {
            return new Response($response->getContent(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename=myPdf.pdf',
            ]);
        } catch (Exception $e) {
            return new Response('Erreur lors de la création de la réponse PDF : ' . $e->getMessage(), 500);
        }
    }

    public function generatePdfFromHtmlFile(string $filePath): Response
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException('Le fichier HTML spécifié est introuvable : ' . $filePath);
        }
        $htmlContent = file_get_contents($filePath);
        return $this->generatePdfFromHtml($htmlContent);
    }

    /**
     * Méthode pour gérer les exceptions non traitées globalement.
     */
    public function handleException(Exception $e): Response
    {
        return new Response('Une erreur inattendue s\'est produite : ' . $e->getMessage(), 500);
    }
}
