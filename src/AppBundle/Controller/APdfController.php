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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

abstract class APdfController extends Controller
{
    /**
     * @param string|array $html One page or array of pages
     * @param array $mPdfConfiguration mode, format, default_font_size, default_font, margin_left, margin_right, margin_top, margin_bottom, margin_header, margin_footer, orientation
     */
    protected function getPdfResponse($title, $html, array $mPdfConfiguration = array('', 'A4' ,'','',0,0,0,0,0,0,'P'))
    {
        define("_MPDF_TEMP_PATH", $this->get('kernel')->getRootDir() . '/../var/mpdf/');
        define("_MPDF_TTFONTDATAPATH", $this->get('kernel')->getRootDir() . '/../var/mpdf/fonts/');

        /** @var \TFox\MpdfPortBundle\Service\MpdfService */
        $mpdfService = $this->get('tfox.mpdfport');

        if (count($mPdfConfiguration) > 0)
            $mpdfService->setAddDefaultConstructorArgs(false);

        /** @var \mPDF */
        $mpdf = $mpdfService->getMpdf($mPdfConfiguration);

        $mpdf->setTitle($title);

        if (is_array($html))
        {
            foreach ($html as $page)
            {
                $mpdf->AddPage();
                $mpdf->writeHTML($page);
            }
        }
        else
        {
            $mpdf->writeHTML($html);
        }

        return new Response($mpdf->Output());
    }
}
