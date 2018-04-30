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
     * @Route("/users/{page}", name="admin_users")
     */
    public function userAction($page)
    {
        $em = $this->getDoctrine()->getManager();
        
        $userQuery = $em->getRepository("AppBundle:User")->findAll();
        
        
        return $this->render('admin/user/index.html.twig',
                ['users' => $userQuery]);
    }
    
    /**
     * @Route("/sales", name="admin_sales")
     */
    public function viewSalesAction()
    {
        return $this->redirectToRoute('track_index', ['only' => 'sold']);
    }
}
