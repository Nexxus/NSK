<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use AppBundle\Entity\SalesOrder;

/**
 * @Route("/track/salesorder")
 */
class SalesOrderController extends Controller
{
    /**
     * @Route("/", name="salesorder_index")
     */
    public function indexAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository(SalesOrder::class);

        $orders = array();

        $form = $this->createFormBuilder(array(), array('allow_extra_fields' => true))
            ->add('query', TextType::class, ['label' => false, 'attr' => ['placeholder' => 'Zoeken op ordernummer']])
            ->add('submit', SubmitType::class, ['label' => 'Search'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $data = $form->getData();
            $orders = $repo->findBySearchQuery($data['query']);
        }
        else
        {
            $orders = $repo->findAll();
        }

        $paginator = $this->get('knp_paginator');
        $ordersPage = $paginator->paginate($orders, $request->query->getInt('page', 1), 10);

        return $this->render('AppBundle:SalesOrder:index.html.twig', array(
            'orders' => $ordersPage,
            'form' => $form->createView()
            ));
    }




   /**
     * @Route("/inlist/{class}/{id}", name="salesorder_inlist")
     * @Method("GET")
     * @param string $entity Full entity name of object holding the orders collection association
     */
    public function inlistAction($entity, $id)
    {
        $object = $this->getDoctrine()->getEntityManager()->find($entity, $id);
        $orders = $object->getSalesOrders();

        return $this->render('AppBundle:SalesOrder:inlist.html.twig', array(
            'orders' => $orders
        ));
    }
}