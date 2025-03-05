<?php
namespace App\Service;

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
        $boundary = '----WebKitFormBoundary' . bin2hex(random_bytes(16));

        $body = "--$boundary\r\n";
        $body .= "Content-Disposition: form-data; name=\"files\"; filename=\"index.html\"\r\n";
        $body .= "Content-Type: text/html\r\n\r\n";
        $body .= $htmlContent . "\r\n";
        $body .= "--$boundary--\r\n";

        $response = $this->client->request('POST', "{$this->gotenbergUrl}/forms/chromium/convert/html", [
            'headers' => [
                'Content-Type' => "multipart/form-data; boundary=$boundary",
                'Accept' => 'application/pdf',
            ],
            'body' => $body,
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Erreur lors de la génération du PDF : ' . $response->getContent(false));
        }

        return new Response($response->getContent(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="document.pdf"',
        ]);
    }

    public function generatePdfFromHtmlFile(string $filePath): Response
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException('Le fichier HTML spécifié est introuvable : ' . $filePath);
        }
        $htmlContent = file_get_contents($filePath);
        return $this->generatePdfFromHtml($htmlContent);
    }
}