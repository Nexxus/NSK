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
     * @Route("/track/exportquery")
     * @Method("GET")
     */
    public function exportQueryAction() {
        $em = $this->getDoctrine()->getManager();
        
        $response = new Response(
                "Test",
                Response::HTTP_OK,
                array('content-type' => 'text/html'));
        
        return $response;
    }
}
