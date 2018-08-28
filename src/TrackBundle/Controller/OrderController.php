<?php

namespace TrackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
* @Route("/track/stock")
*/
class OrderController extends Controller
{
    /**
     * @Route("/index")
     */
    public function indexAction()
    {
        return $this->render('TrackBundle:Order:index.html.twig', array(
            // ...
        ));
    }

    /**
     * @Route("/edit")
     */
    public function editAction()
    {
        return $this->render('TrackBundle:Order:edit.html.twig', array(
            // ...
        ));
    }

    /**
     * @Route("/view")
     */
    public function showAction()
    {
        return $this->render('TrackBundle:Order:show.html.twig', array(
            // ...
        ));
    }

    /**
     * @Route("/delete")
     */
    public function deleteAction()
    {
        return $this->render('TrackBundle:Order:delete.html.twig', array(
            // ...
        ));
    }

}
