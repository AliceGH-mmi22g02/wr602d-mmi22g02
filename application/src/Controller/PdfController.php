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
        GotenbergService       $gotenbergService,
        EntityManagerInterface $em,
        FileRepository         $fileRepository
    )
    {
        $this->gotenbergService = $gotenbergService;
        $this->em = $em;
        $this->fileRepository = $fileRepository;
    }

    /**
     * @Route("/generate-pdf", name="generate_pdf")
     */
    public function generatePdf(Request $request): Response
    {
        // Formulaire pour télécharger un fichier HTML
        $formFile = $this->createFormBuilder()
            ->add('htmlFile', FileType::class, [
                'label' => 'Votre fichier HTML :',
                'mapped' => false,
                'required' => true,
            ])
            ->getForm();

        // Formulaire pour saisir du contenu HTML directement
        $formContent = $this->createFormBuilder()
            ->add('htmlContent', TextareaType::class, [
                'label' => 'Votre contenu HTML :',
                'mapped' => false,
                'required' => true,
            ])
            ->getForm();

        // Traitement du formulaire pour fichier HTML
        $formFile->handleRequest($request);

        // Traitement du formulaire pour contenu HTML
        $formContent->handleRequest($request);

        if ($formFile->isSubmitted() && $formFile->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $formFile->get('htmlFile')->getData();

            if ($uploadedFile) {
                $filePath = $uploadedFile->getRealPath();
                $originalFileName = $uploadedFile->getClientOriginalName();

                try {
                    $user = $this->getUser();
                    if (!$user instanceof User) {
                        return new Response('Utilisateur non connecté.', 403);
                    }

                    $subscription = $user->getSubscription();
                    if (!$subscription) {
                        return new Response('Aucune souscription trouvée.', 403);
                    }

                    $maxPdfPerMonth = $subscription->getMaxPdf();

                    $startOfMonth = new DateTimeImmutable('first day of this month 00:00:00');
                    $endOfMonth = new DateTimeImmutable('last day of this month 23:59:59');

                    $pdfCount = $this->fileRepository->countPdfGeneratedByUserOnDate(
                        $user->getId(),
                        $startOfMonth,
                        $endOfMonth
                    );

                    if ($pdfCount >= $maxPdfPerMonth) {
                        return $this->render('pdf/max_pdf.html.twig');
                    }

                    // Génération du PDF à partir du fichier HTML
                    $pdfResponse = $this->gotenbergService->generatePdfFromHtmlFile($filePath);

                    // Sauvegarde de l'information du fichier dans la base de données
                    $file = new File();
                    $file->setName($originalFileName);
                    $file->setCreatedAt(new DateTimeImmutable());
                    $file->setUser($user);

                    $this->em->persist($file);
                    $this->em->flush();

                    return $pdfResponse;
                } catch (RuntimeException $e) {
                    return new Response($e->getMessage(), 500);
                }
            }
        }

        if ($formContent->isSubmitted() && $formContent->isValid()) {
            $htmlContent = $formContent->get('htmlContent')->getData();

            try {
                $user = $this->getUser();
                if (!$user instanceof User) {
                    return new Response('Utilisateur non connecté.', 403);
                }

                $subscription = $user->getSubscription();
                if (!$subscription) {
                    return new Response('Aucune souscription trouvée.', 403);
                }

                $maxPdfPerMonth = $subscription->getMaxPdf();
                $startOfMonth = new DateTimeImmutable('first day of this month 00:00:00');
                $endOfMonth = new DateTimeImmutable('last day of this month 23:59:59');

                $pdfCount = $this->fileRepository->countPdfGeneratedByUserOnDate(
                    $user->getId(),
                    $startOfMonth,
                    $endOfMonth
                );

                if ($pdfCount >= $maxPdfPerMonth) {
                    return $this->render('pdf/max_pdf.html.twig');
                }

                // Génération du PDF à partir du contenu HTML
                $pdfResponse = $this->gotenbergService->generatePdfFromHtml($htmlContent);

                // Sauvegarde de l'information du fichier dans la base de données
                $file = new File();
                $file->setName('Contenu HTML');
                $file->setCreatedAt(new DateTimeImmutable());
                $file->setUser($user);

                $this->em->persist($file);
                $this->em->flush();

                return $pdfResponse;
            } catch (RuntimeException $e) {
                return new Response($e->getMessage(), 500);
            }
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            return new Response('Utilisateur non connecté.', 403);
        }

        $subscription = $user->getSubscription();
        if (!$subscription) {
            return new Response('Aucune souscription trouvée.', 403);
        }

        $maxPdfPerMonth = $subscription->getMaxPdf();

        $startOfMonth = new DateTimeImmutable('first day of this month 00:00:00');
        $endOfMonth = new DateTimeImmutable('last day of this month 23:59:59');

        $pdfCount = $this->fileRepository->countPdfGeneratedByUserOnDate(
            $user->getId(),
            $startOfMonth,
            $endOfMonth
        );

// Passer les variables au template
        return $this->render('pdf/generate_pdf.html.twig', [
            'formFile' => $formFile->createView(),
            'formContent' => $formContent->createView(),
            'maxPdf' => $maxPdfPerMonth,
            'nbpdf' => $pdfCount,
        ]);

    }
}
