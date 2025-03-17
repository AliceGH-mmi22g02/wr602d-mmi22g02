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

        // Vérifier si l'utilisateur est connecté
        if ($user) {
            // Récupérer tous les fichiers de l'utilisateur triés par date de création (du plus récent au plus ancien)
            $files = $fileRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);
        } else {
            // Si l'utilisateur n'est pas connecté, il n'y a pas de fichiers à afficher
            $files = [];
        }

        return $this->render('history/index.html.twig', [
            'files' => $files,
        ]);
    }
}
