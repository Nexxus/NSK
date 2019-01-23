<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\SalesOrder;
use AppBundle\Entity\Repair;
use AppBundle\Entity\SalesService;
use AppBundle\Entity\Customer;
use AppBundle\Entity\Product;
use AppBundle\Entity\PurchaseOrder;
use AppBundle\Entity\ProductOrderRelation;
use AppBundle\Form\IndexSearchForm;
use AppBundle\Form\SalesOrderForm;
use Symfony\Component\Form\FormError;

/**
 * @Route("/salesorder")
 */
class SalesOrderController extends Controller
{
    use PdfControllerTrait;

    /**
     * @Route("/", name="salesorder_index")
     */
    public function indexAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository(SalesOrder::class);

        $orders = array();

        $container = new \AppBundle\Helper\IndexSearchContainer();
        $container->user = $this->getUser();
        $container->className = SalesOrder::class;

        $form = $this->createForm(IndexSearchForm::class, $container);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $container->isSearchable())
        {
            $orders = $repo->findBySearchQuery($container);
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
     * @Route("/new/{productId}/{isRepair}", name="salesorder_new")
     * @Route("/edit/{id}/{success}", name="salesorder_edit")
     */
    public function editAction(Request $request, $id = 0, $productId = 0, $isRepair = false, $success = null)
    {
        $isRepair = $isRepair ? true : false; // from loose to strict

        $em = $this->getDoctrine()->getManager();

        /** @var \AppBundle\Repository\SalesOrderRepository */
        $repo = $em->getRepository('AppBundle:SalesOrder');

        if ($id == 0)
        {
            $order = new SalesOrder();
            $order->setOrderDate(new \DateTime());
            $stock = $em->getRepository('AppBundle:Product')->findStock($this->getUser());

            if ($productId > 0)
            {
                $sellProduct = $em->find(Product::class, $productId);
                $r = new ProductOrderRelation($sellProduct, $order);
                $r->setPrice($sellProduct->getPrice());
                $r->setQuantity(1);
                $em->persist($r);
            }
        }
        else
        {
            /** @var SalesOrder */
            $order = $repo->find($id);

            // Here is a bug on production environment, but cannot reproduce it locally
            if (!$order) die("Here is a bug on production environment, but cannot reproduce it locally. Id is " . $id);

            $stock = $em->getRepository('AppBundle:Product')->findStockAndNotYetInOrder($this->getUser(), $order);
        }

        $form = $this->createForm(SalesOrderForm::class, $order, array('user' => $this->getUser(), 'stock' => $stock, 'isRepair' => $isRepair));

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
                    $backorder = $form->has('backorder') ? $form->get('backorder')->getData() : false;
                    $repairorder = $form->has('repairorder') ? $form->get('repairorder')->getData() : false;

                    if ($backorder && $repairorder)
                    {
                        $form->get('remarks')->addError(new FormError('Order cannot be repair and back order simultaneously'));
                        $success = false;
                    }
                    else
                    {
                        if ($backorder) // new sales order being backorder
                        {
                            $purchase = new PurchaseOrder();
                            $purchase->setLocation($order->getLocation());
                            $purchase->setOrderDate(new \DateTime());
                            $purchase->setStatus($em->getRepository('AppBundle:OrderStatus')->findOrCreate("Backorder", true, false));

                            $remarks = $order->getRemarks() ? $order->getRemarks() : "Created by backorder";
                            $purchase->setRemarks($remarks);
                            
                            $em->persist($purchase);
                            $order->setBackingPurchaseOrder($purchase);
                        }
                        if ($repairorder) // new sales order being repair
                        {
                            $repair = new Repair($order);
                            $em->getRepository('AppBundle:Repair')->generateBaseServices($repair);
                            $em->persist($repair);
                            $order->setStatus($em->getRepository('AppBundle:OrderStatus')->findOrCreate("To repair", false, true));
                        }
                        elseif ($id) // existing sales order
                        {
                            if ($form->has('addProduct') && $addProduct = $form->get('addProduct')->getData()) // not being backorder or repair
                            {
                                $r = new ProductOrderRelation($addProduct, $order);
                                $r->setPrice($addProduct->getPrice());
                                $r->setQuantity(1);
                                $em->persist($r);
                            }

                            if ($form->has('newService') && $newServiceRelation = $form->get('newService')->getData())
                            {
                                $service = new SalesService($newServiceRelation);
                                $service->setDescription("New service");
                                $em->persist($service);
                            }
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
     * @Route("/deleterelation/{id}", name="salesorder_delete_relation")
     */
    public function deleteRelationAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $relation = $em->find(ProductOrderRelation::class, $id);
        $em->remove($relation);
        $em->flush();

        return $this->redirectToRoute('salesorder_edit', ['id' => $id, 'success' => true]);
    }

    /**
     * @Route("/deleteservice/{id}/{orderId}", name="salesorder_delete_service")
     */
    public function deleteServiceAction($id, $orderId)
    {
        $em = $this->getDoctrine()->getManager();
        $service = $em->find(SalesService::class, $id);
        $em->remove($service);
        $em->flush();

        return $this->redirectToRoute('salesorder_edit', ['id' => $orderId, 'success' => true]);
    }

    /**
     * @Route("/printrepair/{id}/{relationId}", name="salesorder_repair_print")
     */
    public function printRepairAction(Request $request, $id, $relationId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Repair */
        $repair = $em->find(Repair::class, $id);
        $relation = $em->find(ProductOrderRelation::class, $relationId);

        $html = $this->render('AppBundle:SalesOrder:printrepair.html.twig', array('repair' => $repair, 'relation' => $relation));
        $mPdfConfiguration = ['', 'A4' ,'','',10,10,10,10,0,0,'P'];

        return $this->getPdfResponse("Repair", $html, $mPdfConfiguration);
    }

    /**
     * @Route("/invoice/{id}", name="salesorder_invoice")
     */
    public function printInvoiceAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var SalesOrder */
        $order = $em->find(SalesOrder::class, $id);

        $html = $this->render('AppBundle:SalesOrder:invoice.html.twig', array('order' => $order));
        $mPdfConfiguration = ['', 'A4' ,'','',10,10,10,10,0,0,'P'];

        return $this->getPdfResponse("Invoice", $html, $mPdfConfiguration);
    }
}