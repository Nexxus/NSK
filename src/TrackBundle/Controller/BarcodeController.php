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

namespace TrackBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class BarcodeController extends Controller
{
    /**
     * Prints a barcode PDF page
     * 
     * @Route("print/barcode/single/{sku}", name="track_print_barcode")
     */
    public function printAction($sku)
    {
        $mpdfService = $this->get('tfox.mpdfport');
        
        // needs to change page size, turn off default arguments
        $mpdfService->setAddDefaultConstructorArgs(false);
        
        $mpdf = $mpdfService->getMpdf(['', [54,25] ,'9','',3,'3',1,'','0','0','P']);
        
        $html = '<html>
                    <head><META HTTP-EQUIV="Window-target" CONTENT="_blank">
                    <style>
                        body {font-family: sans-serif; font-size: 9pt;}
                        table {border-collapse: collapse; border: 0; }
                        .barcode { padding: 0.5mm; margin: 0; vertical-align: top; color: #000000; }
                        .barcodecell { text-align: center; vertical-align: top; padding: 0; }
                    </style>
                    </head>
                    <body>
                        <table class="items" cellpadding="0" border="0";>
                            <tr>
                                <td style="text-align:center;"><b>Copiatek</b></td>
                            </tr>
                            <tr>
                                <td class="barcodecell">
                                    <barcode code="'.$sku.'" type="C39" class="barcode" size=1 height=1.1/>
                                    &nbsp;
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align:center;"><b>*&nbsp;'.$sku.'&nbsp;*</b></td>
                            </tr>
                        </table>
                    </body>
		</html>';
        
        $mpdf->setTitle("ERCPv2 Barcode Generator");
        $mpdf->writeHTML($html);
        
        return new Response($mpdf->Output());
    }
    
    /**
     * Prints a barcode PDF page
     * 
     * Constructor args:
     * mode - default ''
     * format - A4, for example, default ''	 
     * font size - default 0
     * default font family	
     * margin_left	 
     * margin right	 
     * margin top	
     * margin bottom	
     * margin header	
     * margin footer
     * L - landscape, P - portrait
     * 
     * @Route("print/barcode/multi", name="track_print_barcode_multi")
     */
    public function printMultiAction(Request $request) {
        
        $mpdfService = $this->get('tfox.mpdfport');
        
        
        // needs to change page size, turn off default arguments
        $mpdfService->setAddDefaultConstructorArgs(false);
        
        
        $mpdf = $mpdfService->getMpdf(['', [54,25] ,'0','',0,0,0,0,0,0,'P']);
        
        $mpdf->setTitle("ERCPv2 Barcode Generator");

        // retrieve ids
        $ids = $_GET['id'];
        
        $repo = $this->getDoctrine()->getRepository('TrackBundle:Product');
        
        $items = $repo->findBy([
            'id' => $ids
        ]);
        
        // print barcodes
	foreach($items as $row) {
            $sku = $row->getSku();
            $mpdf->AddPage();
            $html = '<html>
                        <head><META HTTP-EQUIV="Window-target" CONTENT="_blank">
                        <style>
                            body {font-family: sans-serif; font-size: 9pt;}
                            table {border-collapse: collapse; border: 0; }
                            .barcode { padding: 0.5mm; margin: 0; vertical-align: top; color: #000000; }
                            .barcodecell { text-align: center; vertical-align: top; padding: 0; }
                        </style>
                        </head>
                        <body>
                            <table class="items" cellpadding="0" border="0";>
                                <tr>
                                    <td style="text-align:center;"><b>Copiatek</b></td>
                                </tr>
                                <tr>
                                    <td class="barcodecell">
                                        <barcode code="'.$sku.'" type="C39" class="barcode" size=1 height=1.1/>
                                        &nbsp;
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align:center;"><b>*&nbsp;'.$sku.'&nbsp;*</b></td>
                                </tr>
                            </table>
                        </body>
                    </html>';

            $mpdf->writeHTML($html);
            
        }
        
        return new Response($mpdf->Output());
        
    }
    
    /**
     * Prints a barcode PDF page
     * 
     * @Route("print/barcode/multi/a4", name="track_print_barcode_a4")
     */
    public function printMultiA4(Request $request) {
        
        $mpdfService = $this->get('tfox.mpdfport');
        
        
        // needs to change page size, turn off default arguments
        $mpdfService->setAddDefaultConstructorArgs(false);
        
        
        $mpdf = $mpdfService->getMpdf();
        
        $mpdf->setTitle("ERCPv2 Barcode Generator");

        // retrieve ids
        $ids = $_GET['id'];
        
        $repo = $this->getDoctrine()->getRepository('TrackBundle:Product');
        
        $items = $repo->findBy([
            'id' => $ids
        ]);
        
        $html = '<html>
                        <head><META HTTP-EQUIV="Window-target" CONTENT="_blank">
                        <style>
                            body {font-family: sans-serif; font-size: 9pt;}
                            table {border-collapse: collapse; border: 0; }
                            .barcode { padding: 0.5mm; margin: 0; vertical-align: top; color: #000000; }
                            .barcodecell { text-align: center; vertical-align: top; padding: 0; }
                        </style>
                        </head>
                        <body>';
        // print barcodes
        //echo count($items);

        $mpdf->AddPage();
        
        // open table
        $html .= "<h1 style='text-align:center;'>Nexxus Barcode Print</h1>";
        $html .= "<table><tr>";
        $pc = 0; // pagecounter
        $row = 1; $column = 0;
        
        for($i=0;$i<count($ids);$i++) {
            // new row every 6
            if($column>6) {
                $html .= "</tr><tr>";
                $row++;
                $column=1;
            } else {
                $column++;
            }
            $html .= '<td style="text-align:center;">'
                    . '<b>Copiatek</b><br>'
                    . '<barcode code="'.$ids[$i].'" type="C39" class="barcode" size=1 height=1.1/>'
                    . '<br>* '.$ids[$i].' *'
                    . '</td>';
        }
        
        $html .= '</table></body></html>';
        
        //echo "<plaintext>"; print_r($html); exit;
        
        $mpdf->writeHTML($html);
        
        return new Response($mpdf->Output());
        
    }
}
