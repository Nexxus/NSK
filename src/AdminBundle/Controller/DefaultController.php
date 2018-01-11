<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
* @Route("/admin")
*/
class DefaultController extends Controller
{
    /**
     * @Route("/", name="admin_index")
     */
    public function indexAction()
    {
        return $this->render('admin/index.html.twig');
    }
    
    /**
     * @Route("/sales", name="admin_sales")
     */
    public function viewSalesAction()
    {
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->getRepository('TrackBundle:Product')->createQueryBuilder('p')
                ->where('p.status = 999 OR p.status IS NULL')
                ->orderBy('p.updatedAt', 'DESC')
                ->getQuery();
        
        $products = $query->getResult();

        return $this->render('product/index.html.twig', array(
            'products' => $products,
        ));
    }
}
