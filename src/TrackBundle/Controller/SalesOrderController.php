<?php

namespace TrackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/track/salesorder")
 */
class SalesOrderController extends Controller
{
    /**
     * @Route("/inlist/{class}/{id}", name="salesorder_inlist")
     * @Method("GET")
     * @param string $entity Full entity name of object holding the orders collection association
     */
    public function inlistAction($entity, $id)
    {
        $object = $this->getDoctrine()->getEntityManager()->find($entity, $id);
        $orders = $object->getSalesOrders();

        return $this->render('TrackBundle:SalesOrder:inlist.html.twig', array(
            'orders' => $orders
        ));
    }
}