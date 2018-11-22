<?php

/*
 * Nexxus Stock Keeping (online voorraad beheer software)
 * Copyright (C) 2018 Copiatek Scan & Computer Solution BV
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see licenses.
 *
 * Copiatek – info@copiatek.nl – Postbus 547 2501 CM Den Haag
*/

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class BarcodeController extends Controller
{
    use PdfControllerTrait;

    /**
     * @Route("barcode/single/{barcode}", name="barcode_single")
     */
    public function singleAction($barcode)
    {
        $html = $this->render('AppBundle:Barcode:single.html.twig', array(
            'barcode' => $barcode
            ));

        $mPdfConfiguration = ['', [54,25] ,'9','',3,'3',1,'','0','0','P'];

        return $this->getPdfResponse("Nexxus Barcode", $html, $mPdfConfiguration);
    }

    /**
     * @Route("barcode/multi/{barcodes}", name="barcode_multi")
     */
    public function multiAction(array $barcodes)
    {
        $html = array();

        foreach ($barcodes as $barcode)
        {
            $html[] = $this->render('AppBundle:Barcode:single.html.twig', array(
            'barcode' => $barcode
            ));
        }

        $mPdfConfiguration = ['', [54,25] ,'9','',3,'3',1,'','0','0','P']; // or ['', [54,25] ,'0','',0,0,0,0,0,0,'P']

        return $this->getPdfResponse("Nexxus Barcode", $html, $mPdfConfiguration);
    }

    /**
     * @Route("barcode/a4/{barcodes}", name="barcode_multi_a4")
     */
    public function multiA4Action(array $barcodes)
    {
        $html = $this->render('AppBundle:Barcode:a4.html.twig', array(
            'barcodes' => $barcodes
            ));

        return $this->getPdfResponse("Nexxus Barcode", $html);
    }



    /*
     * $mpdf->AddPage(); to generate multiple stickers
     *
     * or use a4 template for multiple barcodes on one A4
     */
}
