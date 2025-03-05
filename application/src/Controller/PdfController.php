<?php

// src/Controller/PdfController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\GotenbergService;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PdfController extends AbstractController
{
    private GotenbergService $gotenbergService;

    public function __construct(GotenbergService $gotenbergService)
    {
        $this->gotenbergService = $gotenbergService;
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

                try {
                    return $this->gotenbergService->generatePdfFromHtmlFile($filePath);
                } catch (\RuntimeException $e) {
                    return new Response($e->getMessage(), 500);
                }
            }
        }

        return $this->render('pdf/generate_pdf.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}