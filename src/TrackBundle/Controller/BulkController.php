<?php

namespace TrackBundle\Controller;

use TrackBundle\Entity\Product;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
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
        foreach($products as $product) {
            //...
        }
        
        // check if all products have attributes
        
        // show form for attributes
        
        return $this->render('TrackBundle:Bulk:edit.html.twig', array(
        ));
    }

}
