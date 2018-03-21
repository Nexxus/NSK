<?php

namespace TrackBundle\Controller;

use TrackBundle\Entity\Product;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for editing multiple products in one form
 * 
 * @Route("/track/bulk")
 */
class BulkController extends ProductController
{
    /**
     * @Route("/edit", name="track_bulk_edit")
     * @Method({"GET"})
     */
    public function bulkEditAction(Request $request)
    {
        $ids = $request->query->get('id');
        
        $em = $this->getDoctrine()->getManager();
        
        // get items by GET ids
        $products = $em->getRepository("TrackBundle:Product")->createQueryBuilder('q');
        
        $whereIn  = "(";
        
        foreach($ids as $id) {
            $whereIn .= $id . ",";
        }
        
        $whereIn = rtrim($whereIn, ",") . ")";
        
        $products = $products->where("q.id IN ".$whereIn);
        
        $products = $products->getQuery()->getResult();
        
        // if all items are the same type, give attribute options
        echo $this->ifProductTypeEqual($products) ? 'true' : 'false';
        
        // check if all products have attributes
        
        // show form for attributes
        
        return $this->render('TrackBundle:Bulk:edit.html.twig', array(
        ));
    }

    /**
     * Loops through array of products 
     * returns true if products are the same type
     * 
     * @param type $products
     * @return boolean
     */
    public function ifProductTypeEqual($products) {
        $equal = true; 
        $lasttype = ""; 
        
        $i=0; 
        foreach($products as $product) {
            if($i>0 && ($lasttype != $product->getType())) {
                $equal = false;
            }
            
            $lasttype = $product->getType();
            $i++;
        }
        
        return $equal;
    }
    
}
