<?php

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
     * @Route("print/barcode39/{sku}", name="track_print_barcode")
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
     * @Route("print/barcode39multi", name="track_print_barcode_multi")
     */
    public function printMultiAction(Request $request) {
        
        $mpdfService = $this->get('tfox.mpdfport');
        
        
        // needs to change page size, turn off default arguments
        $mpdfService->setAddDefaultConstructorArgs(false);
        
        $mpdf = $mpdfService->getMpdf(['', [54,25] ,'9','',3,'3',1,'','0','0','P']);
        
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
}
