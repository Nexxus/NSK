<?php

namespace TrackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use TrackBundle\Entity\Product;
use TrackBundle\Entity\ProductTask;

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
        
        $tasks = $em->getRepository("TrackBundle:ProductTask")->createQueryBuilder('t')
                ->where('t.productId = :q')
                ->setParameter('q', $product->getId())
                ->getQuery();
        
        $tasks = $tasks->getResult();
        
        return $this->render("product/checklist/list.html.twig", [
            'product' => $product,
            'tasks' => $tasks,
        ]);
    }
}
