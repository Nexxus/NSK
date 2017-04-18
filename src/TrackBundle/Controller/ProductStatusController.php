<?php

namespace TrackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/admin/status")
 */
class ProductStatusController extends Controller
{
    /**
     * @Route("/", name="status_index")
     */
    public function indexAction() 
    {
        $productstatus = $this->getDoctrine()
                ->getRepository('TrackBundle:ProductStatus')
                ->findAll();
        
        return $this->render('admin/status/index.html.twig', array(
            'productstatus' => $productstatus)); 
    }
    
    /**
     * @Route("/edit/{id}", name="status_edit")
     */
    public function editAction($id)
    {
        
    }
}
