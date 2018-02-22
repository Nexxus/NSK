<?php

namespace TrackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
    public function exportCSVAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        
        $selection = $request->query->get('id');
        
        // get items
        $query = $em->getRepository("TrackBundle:Product")->createQueryBuilder('p');
        
        foreach($selection as $id) {
            $query->orWhere("p.id = {$id}");
        }
        
        $products = $query->getQuery()->getResult();
        
        // serialize into text
        $serialize = "";
        
        foreach($products as $product) {

        }
        
        $response = new Response(
                $serialize,
                Response::HTTP_OK,
                array('content-type' => 'text/html'));
        
        return $response;
    }
}
