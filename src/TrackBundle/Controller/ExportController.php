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
        $serialize = "SKU"
                . "\t" . "Name"
                . "\t" . "Quantity"
                . "\t" . "Location"
                . "\t" . "Type"
                . "\t" . "Status"
                . "\t" . "Brand"
                . "\t" . "Department"
                . "\t" . "Owner";
        
        foreach($products as $product) {
            $serialize .= "\n" . $product->getSku()
                        . "\t" . $product->getName()
                        . "\t" . $product->getQuantity()
                        . "\t" . $product->getLocation()
                        . "\t" . $product->getType()
                        . "\t" . $product->getStatus()
                        . "\t" . $product->getBrand()
                        . "\t" . $product->getDepartment() 
                        . "\t" . $product->getOwner();
        }
        
        $response = new Response(
                $serialize,
                Response::HTTP_OK,
                [
                    'Content-Disposition' => 'attachment; Filename=ProductExport.csv',
                    'Content-Type' => 'text/csv'
                ]);
        
        return $response;
    }
}
