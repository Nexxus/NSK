<?php

namespace TrackBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use TFox\MpdfPortBundle;

class BarcodeController extends Controller
{
    /**
     * Prints a barcode PDF page
     * 
     * @Route("print/barcode39/{sku}/", name="track_print_barcode")
     */
    public function printAction($sku)
    {
        $mpdfService = $this->get('tfox.mpdfport');
        $mpdfService->setAddDefaultConstructorArgs(false);
        
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
        
        return $mpdfService->generatePdfResponse($html, array('', array(54,25) ,'9','',3,'3',1,'','0','0','P'));
    }
}
