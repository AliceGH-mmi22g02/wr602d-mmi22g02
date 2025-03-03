<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;

class GotenbergService
{
    private HttpClientInterface $client;
    private string $gotenbergUrl;

    public function __construct(HttpClientInterface $client, ParameterBagInterface $params)
    {
        $this->client = $client;
        $this->gotenbergUrl = $params->get('gotenberg_url');  // Accède à la variable 'gotenberg_url' dans le fichier de configuration
    }

    // Méthode pour générer un PDF à partir du contenu HTML
    public function generatePdfFromHtml(string $htmlContent): Response
    {
        $response = $this->client->request('POST', "{$this->gotenbergUrl}/forms/chromium/convert/html", [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
            ],
            'body' => [
                'files' => [
                    'index.html' => fopen('data://text/plain,' . $htmlContent, 'r'),
                ],
            ],
        ]);

        // Vérifier si la requête a réussi
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Erreur lors de la génération du PDF : ' . $response->getContent(false));
        }

        // Retourner la réponse Symfony avec le contenu du PDF
        return new Response($response->getContent(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="document.pdf"',
        ]);
    }

    // Méthode pour générer un PDF à partir d'un fichier HTML
    public function generatePdfFromHtmlFile(string $filePath): Response
    {
        // Lire le contenu du fichier HTML
        $htmlContent = file_get_contents($filePath);

        // Appeler la méthode pour générer le PDF
        return $this->generatePdfFromHtml($htmlContent);
    }
}
