<?php

namespace App\Controller;

use App\Service\PdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class TestingController extends AbstractController
{


    /**
     * @Route("/test")
     */
    public function test(PdfService $pdfService) {
            $pdfService -> generatePDF("pdf/pdf.html.twig", $this->getUser());
    }

}
