<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\SalesOrder;
use AppBundle\Entity\Customer;
use AppBundle\Entity\PurchaseOrder;
use AppBundle\Entity\ProductOrderRelation;
use AppBundle\Form\IndexSearchForm;
use AppBundle\Form\SalesOrderForm;
use Symfony\Component\Form\FormError;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Route("/salesorder")
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

        $form = $this->createForm(IndexSearchForm::class, array());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $data = $form->getData();
            $orders = $repo->findBySearchQuery($data['query']);
        }
        else
        {
            $orders = $repo->findMine($this->getUser());
        }

        $paginator = $this->get('knp_paginator');
        $ordersPage = $paginator->paginate($orders, $request->query->getInt('page', 1), 10);

        return $this->render('AppBundle:SalesOrder:index.html.twig', array(
            'orders' => $ordersPage,
            'form' => $form->createView()
            ));
    }

    /**
     * @Route("/edit/{id}/{success}", name="salesorder_edit")
     */
    public function editAction(Request $request, $id = 0, $success = null)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \AppBundle\Repository\SalesOrderRepository */
        $repo = $em->getRepository('AppBundle:SalesOrder');

        if ($id == 0)
        {
            $order = new SalesOrder();
            $order->setOrderDate(new \DateTime());
            $stock = $em->getRepository('AppBundle:Product')->findStock($this->getUser());
        }
        else
        {
            /** @var SalesOrder */
            $order = $repo->find($id);
            $stock = $em->getRepository('AppBundle:Product')->findStockAndNotYetInOrder($this->getUser(), $order);
        }

        $form = $this->createForm(SalesOrderForm::class, $order, array('user' => $this->getUser(), 'stock' => $stock));

        $form->handleRequest($request);

        if (!$order->getLocation())
        {
            $order->setLocation($this->get('security.token_storage')->getToken()->getUser()->getLocation());
        }

        if ($form->isSubmitted())
        {
            if ($form->get('newOrExistingCustomer')->getData() == 'existing')
            {
                if (!$order->getCustomer())
                {
                    $form->get('customer')->addError(new FormError('Please select existing customer'));
                }
            }
            else
            {
                /** @var Customer */
                $newCustomer = $form->get('newCustomer')->getData();
                $newCustomer->addSalesOrder($order);
                $newCustomer->setLocation($order->getLocation());
                $em->persist($newCustomer);
                $order->setCustomer($newCustomer);
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
                    $purchase = null;

                    if ($form->get('backorder')->getData()) // new sales order being backorder
                    {
                        $purchase = new PurchaseOrder();
                        $purchase->setLocation($order->getLocation());
                        $purchase->setRemarks("Created by backorder");
                        $purchase->setOrderDate(new \DateTime());
                        $purchase->setStatus($em->getRepository('AppBundle:OrderStatus')->findOrCreate("Backorder", true, false));
                        $em->persist($purchase);
                        $order->setBackingPurchaseOrder($purchase);


                    }
                    else if ($addProduct = $form->get('addProduct')->getData()) // existing sales order not being backorder
                    {
                        $r = new ProductOrderRelation();
                        $r->setProduct($addProduct);
                        $r->setOrder($order);
                        $r->setPrice($addProduct->getPrice());
                        $r->setQuantity(1);
                        $order->addProductRelation($r);
                        $em->persist($r);
                    }

                    $em->flush();

                    if (!$order->getOrderNr())
                    {
                        $order->setOrderNr($repo->generateOrderNr($order));

                        if ($purchase)
                        {
                            $purchase->setOrderNr($em->getRepository('AppBundle:PurchaseOrder')->generateOrderNr($purchase));
                        }

                        $em->flush();
                    }

                    return $this->redirectToRoute("salesorder_edit", array('id' => $order->getId(), 'success' => true));
                }
            }
            else
            {
                $success = false;
            }
        }

        return $this->render('AppBundle:SalesOrder:edit.html.twig', array(
                'order' => $order,
                'form' => $form->createView(),
                'success' => $success,
            ));

    }

    /**
     * @Route("/delete/{id}", name="salesorder_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $order = $em->find(SalesOrder::class, $id);
        $em->remove($order);
        $em->flush();

        return $this->redirectToRoute('salesorder_index');
    }

    /**
     * @Route("/deleterelation/{id}/{productId}", name="salesorder_delete_relation")
     */
    public function deleteRelationAction($id, $productId)
    {
        $em = $this->getDoctrine()->getManager();
        $relation = $em->getRepository('AppBundle:ProductOrderRelation')->findOneBy(array('order' => $id, 'product' => $productId));
        $em->remove($relation);
        $em->flush();

        return $this->redirectToRoute('salesorder_edit', ['id' => $id, 'success' => true]);
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