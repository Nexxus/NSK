<?php

namespace TrackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use mpdf\mpdf;

class BarcodeController extends Controller
{
    /**
     * @Route("/printBarcode")
     */
    public function printAction()
    {
        //$mpdf = new Mpdf();
        return new Response(
                'Hello World!'
        );
    }

}
