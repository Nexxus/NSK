<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Supplier;
use AppBundle\Entity\Address;
use AppBundle\Entity\PurchaseOrder;
use AppBundle\Entity\Location;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use AppBundle\Form\IndexSearchForm;
use AppBundle\Form\PurchaseOrderForm;
use Symfony\Component\Form\FormError;

/**
 * @Route("/purchaseorder")
 */
class PurchaseOrderController extends Controller
{
    /**
     * @Route("/", name="purchaseorder_index")
     */
    public function indexAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository(PurchaseOrder::class);

        $orders = array();

        $form = $this->createForm(IndexSearchForm::class, array());

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

        return $this->render('AppBundle:PurchaseOrder:index.html.twig', array(
            'orders' => $ordersPage,
            'form' => $form->createView()
            ));
    }

    /**
     * @Route("/edit/{id}/{success}", name="purchaseorder_edit")
     */
    public function editAction(Request $request, $id = 0, $success = null)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \AppBundle\Repository\PurchaseOrderRepository */
        $repo = $em->getRepository('AppBundle:PurchaseOrder');

        if ($id == 0)
        {
            $order = new PurchaseOrder();
            $order->setOrderDate(new \DateTime());
        }
        else
        {
            /** @var PurchaseOrder */
            $order = $repo->find($id);
        }

        $location = $this->get('security.token_storage')->getToken()->getUser()->getLocation();
        $order->setLocation($location);

        $form = $this->createForm(PurchaseOrderForm::class, $order);

        $form->handleRequest($request);

        if ($form->isSubmitted())
        {
            if ($form->get('newOrExistingSupplier')->getData() == 'existing')
            {
                if (!$order->getSupplier())
                {
                    $form->get('supplier')->addError(new FormError('Please select existing supplier'));
                }
            }
            else
            {
                /** @var Supplier */
                $newSupplier = $form->get('newSupplier')->getData();
                $newSupplier->addPurchaseOrder($order);
                $em->persist($newSupplier);
                $order->setSupplier($newSupplier);
            }

            if ($form->isValid())
            {
                $em->persist($order);

                try {
                    $em->flush();
                }
                catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                    $form->get('orderNr')->addError(new FormError('Already exists'));
                    $success = false;
                }

                if ($success !== false)
                {
                    if (!$order->getOrderNr())
                    {
                        $order->setOrderNr($order->getOrderDate()->format("Y") . sprintf('%06d', $order->getId()));
                        $em->flush();
                    }

                    return $this->redirectToRoute("purchaseorder_edit", array('id' => $order->getId(), 'success' => true));
                }
            }
            else
            {
                $success = false;
            }
        }

        return $this->render('AppBundle:PurchaseOrder:edit.html.twig', array(
                'order' => $order,
                'form' => $form->createView(),
                'success' => $success,
            ));

    }

    /**
     * @Route("/delete/{id}", name="purchaseorder_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository('AppBundle:PurchaseOrder')->find($id);
        $em->remove($order);
        $em->flush();

        return $this->redirectToRoute('purchaseorder_index');
    }

    /**
     * @Route("/inlist/{class}/{id}", name="purchaseorder_inlist")
     * @Method("GET")
     * @param string $entity Full entity name of object holding the orders collection association
     */
    public function inlistAction($entity, $id)
    {
        $object = $this->getDoctrine()->getEntityManager()->find($entity, $id);
        $orders = $object->getPurchaseOrders();

        return $this->render('AppBundle:PurchaseOrder:inlist.html.twig', array(
            'orders' => $orders
        ));
    }
}
