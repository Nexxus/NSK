<?php

namespace TrackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Controller for exporting CSVs
 */
class ExportController extends Controller
{
    
    /**
     * Exports a CSV file based on list of items, exports all columns
     * 
     * @Route("/track/export/csv", name="track_export_csv")
     * @Method("GET")
     */
    public function exportCSVAction() {
        $em = $this->getDoctrine()->getManager();
        
        $response = new Response(
                "Test",
                Response::HTTP_OK,
                array('content-type' => 'text/html'));
        
        return $response;
    }
}
