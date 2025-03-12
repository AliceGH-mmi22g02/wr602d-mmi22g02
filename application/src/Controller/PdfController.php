<?php

// src/Controller/PdfController.php

namespace App\Controller;

use DateTimeImmutable;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\GotenbergService;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;

class PdfController extends AbstractController
{
    private GotenbergService $gotenbergService;
    private EntityManagerInterface $em;

    public function __construct(GotenbergService $gotenbergService, EntityManagerInterface $em)
    {
        $this->gotenbergService = $gotenbergService;
        $this->em = $em;
    }

    /**
     * @Route("/generate-pdf", name="generate_pdf")
     */
    public function generatePdf(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('htmlFile', FileType::class, [
                'label' => 'Votre fichier html : ',
                'mapped' => false,
                'required' => true,
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('htmlFile')->getData();

            if ($uploadedFile) {
                $filePath = $uploadedFile->getRealPath();
                $originalFileName = $uploadedFile->getClientOriginalName();  // Nom du fichier téléchargé

                try {
                    // Générer le PDF à partir du fichier HTML
                    $pdfResponse = $this->gotenbergService->generatePdfFromHtmlFile($filePath);

                    // Créer une nouvelle entité File et la remplir
                    $file = new File();
                    $file->setName($originalFileName);  // Enregistrer le nom du fichier téléchargé
                    $file->setCreatedAt(new DateTimeImmutable());

                    // Sauvegarder l'entité File dans la base de données
                    $this->em->persist($file);
                    $this->em->flush();

                    // Retourner la réponse PDF après avoir persisté l'entité File
                    return $pdfResponse;
                } catch (RuntimeException $e) {
                    return new Response($e->getMessage(), 500);
                }
            }
        }

        return $this->render('pdf/generate_pdf.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
