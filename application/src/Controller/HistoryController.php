<?php

namespace App\Controller;

use App\Repository\FileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HistoryController extends AbstractController
{
    #[Route('/history', name: 'app_history')]
    public function index(FileRepository $fileRepository): Response
    {
        // Récupérer tous les fichiers triés par date de création (du plus récent au plus ancien)
        $files = $fileRepository->findFilesByDateDesc();

        return $this->render('history/index.html.twig', [
            'files' => $files,
        ]);
    }
}
