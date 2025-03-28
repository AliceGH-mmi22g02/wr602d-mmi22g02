<?php

// src/Controller/HistoryController.php

namespace App\Controller;

use App\Repository\FileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use DateTimeImmutable;

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

            // Récupérer l'abonnement de l'utilisateur (si existe)
            $subscription = $user->getSubscription();
            $maxPdfPerMonth = $subscription->getMaxPdf();

            $startOfMonth = new DateTimeImmutable('first day of this month 00:00:00');
            $endOfMonth = new DateTimeImmutable('last day of this month 23:59:59');

            // Compter le nombre de PDF générés par l'utilisateur ce mois-ci
            $pdfCount = $fileRepository->countPdfGeneratedByUserOnDate(
                $user->getId(),
                $startOfMonth,
                $endOfMonth
            );
        } else {
            $maxPdfPerMonth = 0;
            $pdfCount = 0;
        }

        return $this->render('history/index.html.twig', [
            'files' => $files,
            'nbpdf' => $pdfCount,
            'maxPdf' => $maxPdfPerMonth,
        ]);
    }
}
