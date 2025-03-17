<?php

// src/Controller/HistoryController.php

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
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Initialiser la variable $files
        $files = [];

        // Si l'utilisateur est connecté, récupérer tous ses fichiers triés par date de création
        if ($user) {
            $files = $fileRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);
        }

        return $this->render('history/index.html.twig', [
            'files' => $files,
        ]);
    }
}