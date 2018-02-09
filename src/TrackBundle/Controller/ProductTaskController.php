<?php

namespace TrackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use TrackBundle\Entity\Product;

/**
 * @Route("track/task")
 */
class ProductTaskController extends Controller
{
    
    /**
     * @Route("/show/{id}", name="track_checklist_show")
     * @Method({"GET"})
     */
    public function showListAction(Product $product) {
        $em = $this->getDoctrine()->getManager();
        
        return $this->render("product/checklist/list.html.twig", [
            'product' => $product,
            'tasks' => [],
        ]);
    }
}
