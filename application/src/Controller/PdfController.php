<?php

namespace App\Controller;

use DateTimeImmutable;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\GotenbergService;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\FileRepository;
use App\Entity\User;

class PdfController extends AbstractController
{
    private GotenbergService $gotenbergService;
    private EntityManagerInterface $em;
    private FileRepository $fileRepository;

    public function __construct(
        GotenbergService $gotenbergService,
        EntityManagerInterface $em,
        FileRepository $fileRepository
    ) {
        $this->gotenbergService = $gotenbergService;
        $this->em = $em;
        $this->fileRepository = $fileRepository;
    }

    /**
     * @Route("/generate-pdf", name="generate_pdf")
     */
    public function generatePdf(Request $request): Response
    {
        // Formulaires pour télécharger un fichier HTML ou saisir du contenu
        $formFile = $this->createFileForm();
        $formContent = $this->createContentForm();

        // Traitement des formulaires
        $formFile->handleRequest($request);
        $formContent->handleRequest($request);

        if ($formFile->isSubmitted() && $formFile->isValid()) {
            return $this->handleFileForm($formFile, $request);
        }

        if ($formContent->isSubmitted() && $formContent->isValid()) {
            return $this->handleContentForm($formContent, $request);
        }

        // Variables utilisateur et souscription
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new Response('Utilisateur non connecté.', 403);
        }

        $subscription = $user->getSubscription();
        if (!$subscription) {
            return new Response('Aucune souscription trouvée.', 403);
        }

        $maxPdfPerMonth = $subscription->getMaxPdf();
        $pdfCount = $this->countPdfGenerated($user);

        // Passer les variables au template
        return $this->render('pdf/generate_pdf.html.twig', [
            'formFile' => $formFile->createView(),
            'formContent' => $formContent->createView(),
            'maxPdf' => $maxPdfPerMonth,
            'nbpdf' => $pdfCount,
        ]);
    }

    private function createFileForm()
    {
        return $this->createFormBuilder()
            ->add('htmlFile', FileType::class, [
                'label' => 'Votre fichier HTML :',
                'mapped' => false,
                'required' => true,
            ])
            ->getForm();
    }

    private function createContentForm()
    {
        return $this->createFormBuilder()
            ->add('htmlContent', TextareaType::class, [
                'label' => 'Votre contenu HTML :',
                'mapped' => false,
                'required' => true,
            ])
            ->getForm();
    }

    private function handleFileForm($formFile, Request $request): Response
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $formFile->get('htmlFile')->getData();

        if ($uploadedFile) {
            $filePath = $uploadedFile->getRealPath();
            $originalFileName = $uploadedFile->getClientOriginalName();

            try {
                $user = $this->getUser();
                $subscription = $user->getSubscription();
                $pdfCount = $this->countPdfGenerated($user);

                if ($pdfCount >= $subscription->getMaxPdf()) {
                    return $this->render('pdf/max_pdf.html.twig');
                }

                // Génération du PDF
                $pdfResponse = $this->gotenbergService->generatePdfFromHtmlFile($filePath);

                // Sauvegarde du fichier
                $this->saveFile($originalFileName, $user);

                return $pdfResponse;
            } catch (RuntimeException $e) {
                return new Response($e->getMessage(), 500);
            }
        }

        return $this->render('pdf/generate_pdf.html.twig', ['formFile' => $formFile->createView()]);
    }

    private function handleContentForm($formContent, Request $request): Response
    {
        $htmlContent = $formContent->get('htmlContent')->getData();

        try {
            $user = $this->getUser();
            $subscription = $user->getSubscription();
            $pdfCount = $this->countPdfGenerated($user);

            if ($pdfCount >= $subscription->getMaxPdf()) {
                return $this->render('pdf/max_pdf.html.twig');
            }

            // Génération du PDF
            $pdfResponse = $this->gotenbergService->generatePdfFromHtml($htmlContent);

            // Sauvegarde du fichier
            $this->saveFile('Contenu HTML', $user);

            return $pdfResponse;
        } catch (RuntimeException $e) {
            return new Response($e->getMessage(), 500);
        }
    }

    private function saveFile(string $fileName, User $user): void
    {
        $file = new File();
        $file->setName($fileName);
        $file->setCreatedAt(new DateTimeImmutable());
        $file->setUser($user);

        $this->em->persist($file);
        $this->em->flush();
    }

    private function countPdfGenerated(User $user): int
    {
        $startOfMonth = new DateTimeImmutable('first day of this month 00:00:00');
        $endOfMonth = new DateTimeImmutable('last day of this month 23:59:59');

        return $this->fileRepository->countPdfGeneratedByUserOnDate(
            $user->getId(),
            $startOfMonth,
            $endOfMonth
        );
    }
}
