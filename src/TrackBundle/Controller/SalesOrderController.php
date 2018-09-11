<?php

namespace TrackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
* @Route("/track/sales")
*/
class SalesOrderController extends Controller
{
    /**
     * @Route("/index")
     */
    public function indexAction()
    {
        return $this->render('TrackBundle:SalesOrder:index.html.twig', array(
            // ...
        ));
    }

    /**
     * @Route("/edit")
     */
    public function editAction()
    {
        return $this->render('TrackBundle:SalesOrder:edit.html.twig', array(
            // ...
        ));
    }

    /**
     * @Route("/view")
     */
    public function showAction()
    {
        return $this->render('TrackBundle:SalesOrder:show.html.twig', array(
            // ...
        ));
    }

    /**
     * @Route("/delete")
     */
    public function deleteAction()
    {
        return $this->render('TrackBundle:SalesOrder:delete.html.twig', array(
            // ...
        ));
    }

}
