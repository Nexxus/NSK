<?php

/*
 * Nexxus Stock Keeping (online voorraad beheer software)
 * Copyright (C) 2018 Copiatek Scan & Computer Solution BV
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see licenses.
 *
 * Copiatek - info@copiatek.nl - Postbus 547 2501 CM Den Haag
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use AppBundle\Entity\SalesOrder;
use AppBundle\Entity\Repair;
use AppBundle\Entity\SalesService;
use AppBundle\Entity\Customer;
use AppBundle\Entity\Product;
use AppBundle\Entity\PurchaseOrder;
use AppBundle\Entity\ProductOrderRelation;
use AppBundle\Form\IndexSearchForm;
use AppBundle\Form\IndexBulkEditForm;
use AppBundle\Form\SalesOrderForm;
use AppBundle\Form\SalesOrderImportForm;
use AppBundle\Form\SalesOrderBulkEditForm;
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
        $repo = $this->getDoctrine()->getRepository(SalesOrder::class );

        $orders = array();

        $container = new \AppBundle\Helper\IndexSearchContainer($this->getUser(), SalesOrder::class);

        $form = $this->createForm(IndexSearchForm::class, $container);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $container->isSearchable())
        {
            $orders = $repo->findBySearchQuery($container);
            $pageLength = 200;
        }
        else
        {
            $orders = $repo->findMine($this->getUser());
            $pageLength = 20;
        }

        $paginator = $this->get('knp_paginator');
        $ordersPage = $paginator->paginate($orders, $request->query->getInt('page', 1), $pageLength);

        return $this->render('AppBundle:SalesOrder:index.html.twig', array(
            'orders' => $ordersPage,
            'form' => $form->createView(),
            'formBulkEdit' => $this->createForm(IndexBulkEditForm::class, $orders)->createView()
        ));
    }

    /**
     * @Route("/bulkedit/{success}", name="salesorder_bulkedit")
     */
    public function bulkEditAction(Request $request, $success = null)
    {
        $em = $this->getDoctrine()->getManager();

        // Get variables from IndexBulkEditForm
        $action = $request->query->get('index_bulk_edit_form')['action'];
        $orderIds = $request->query->get('index_bulk_edit_form')['index'];
        $orders = $em->getRepository(SalesOrder::class)->findBy(['id' => $orderIds]);

        if ($action == "status")
        {
            $form = $this->createForm(SalesOrderBulkEditForm::class, $orders, array('user' => $this->getUser()));

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {
                $status = $form->get("status")->getData();

                foreach ($orders as $order)
                {
                    if ($status)
                        $order->setStatus($status);
                }

                $em->flush();

                return $this->redirectToRoute("salesorder_bulkedit", array('index_bulk_edit_form[action]' => $action, 'index_bulk_edit_form[index]' => $orderIds, 'success' => true));
            }
            else if ($form->isSubmitted())
            {
                $success = false;
            }

            return $this->render('AppBundle:SalesOrder:bulkedit.html.twig', array(
                'form' => $form->createView(),
                'success' => $success
            ));
        }
        else {
            return $this->bulkPrint($action, $orders);
        }
    }

    /**
     * @param string $object
     * @param SalesOrder[] $orders
     */
    private function bulkPrint($object, $orders) {

        $html = array();
        $mPdfConfiguration = ['', 'A4', '', '', 10, 10, 10, 10, 0, 0, 'P'];

        if (!count($orders)) {
            $html = "No (valid) orders to print";
        }
        else
        {
            foreach ($orders as $order)
            {
                /** @var SalesOrder $order */

                switch ($object) {
                    case "orders":
                        $html[] = $this->render('AppBundle:SalesOrder:print.html.twig', array('order' => $order));
                        break;
                }
            }
        }

        //return new Response($html[0]);

        return $this->getPdfResponse("Bulk print", $html, $mPdfConfiguration);
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
            $stock = $em->getRepository('AppBundle:Product')->findStockAndNotYetInOrder($this->getUser(), $order);
        }

        $form = $this->createForm(SalesOrderForm::class, $order, array('user' => $this->getUser(), 'stock' => $stock, 'isRepair' => $isRepair));

        $form->handleRequest($request);

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
                catch (\Exception $e) {
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
                            $order->setOrderNr($repo->generateSalesOrderNr($order));

                            if ($purchase)
                            {
                                $purchase->setOrderNr($em->getRepository('AppBundle:PurchaseOrder')->generatePurchaseOrderNr($purchase));
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
        /** @var ProductOrderRelation */
        $relation = $em->find(ProductOrderRelation::class, $id);
        $em->remove($relation);
        $em->flush();

        return $this->redirectToRoute('salesorder_edit', ['id' => $relation->getOrder()->getId(), 'success' => true]);
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
     * @Route("/print/{id}", name="salesorder_print")
     */
    public function printAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository('AppBundle:SalesOrder')->find($id);

        $html = $this->render('AppBundle:SalesOrder:print.html.twig', array('order' => $order));
        $mPdfConfiguration = ['', 'A4', '', '', 10, 10, 10, 10, 0, 0, 'P'];

        return $this->getPdfResponse("Nexxus sales order", $html, $mPdfConfiguration);
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
        $mPdfConfiguration = ['', 'A4', '', '', 10, 10, 10, 10, 0, 0, 'P'];

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
        $mPdfConfiguration = ['', 'A4', '', '', 10, 10, 10, 10, 0, 0, 'P'];

        return $this->getPdfResponse("Invoice", $html, $mPdfConfiguration);
    }

    /**
     * @Route("/import/{success}", name="salesorder_import")
     */
    public function importAction(Request $request, $success = null)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \AppBundle\Repository\SalesOrderRepository */
        $repo = $em->getRepository('AppBundle:SalesOrder');

        $form = $this->createForm(SalesOrderImportForm::class, null, array('user' => $this->getUser()));

        $form->handleRequest($request);

        if ($form->isSubmitted())
        {
            set_time_limit(3600);

            $data = $form->getData();

            /** @var UploadedFile */
            $file = $data['import'];

            if (!$file)
            {
                $form->addError(new FormError('The file is invalid'));
            }

            if ($form->isValid())
            {
                $content = file_get_contents($file->getRealPath());
                $lines = preg_split("/((\r?\n)|(\r\n?))/", $content);

                // Referentie;Bedrijfsnaam;Voornaam;Achternaam;Straatnaam;Huisnummer;Huisnummer toevoeging;Postcode;Plaatsnaam;Landcode;Email;Telefoon;Mobiel nummer;
                // Gebouw;Verdieping;Afdeling;Deurcode;Aflever referentie
                $keys = explode(";", array_shift($lines));

                foreach ($lines as $line)
                {
                    if (strpos($line, ";;;;;;;;;;;;") !== false) continue;
                    
                    $values = explode(";", $line);

                    if (count($values) != count($keys)) continue;

                    $orderInput = array_combine($keys, $values);

                    if (!$orderInput['Bedrijfsnaam'] && !$orderInput['Voornaam'] && !$orderInput['Achternaam']) continue;

                    try {
                        $remarks = 
                            "Referentie: " . $orderInput['Referentie'] . "\r\n" .
                            "Gebouw: " . $orderInput['Gebouw'] . "\r\n" .
                            "Verdieping: " . $orderInput['Verdieping'] . "\r\n" .
                            "Afdeling: " . $orderInput['Afdeling'] . "\r\n" .
                            "Deurcode: " . $orderInput['Deurcode'] . "\r\n" .
                            "Aflever referentie: " . $orderInput['Aflever referentie'];

                        $order = new SalesOrder();
                        $order->setOrderDate(new \DateTime());
                        $order->setIsGift(false);
                        $order->setStatus($em->getRepository('AppBundle:OrderStatus')->findOrCreate("Products to assign", false, true));
                        $order->setRemarks($remarks);

                        // Leergeld puts partner name in field Bedrijfsnaam :-(
                        if ($orderInput['Bedrijfsnaam'] && strpos($orderInput['Bedrijfsnaam'], "Leergeld") === false)
                            $name = $orderInput['Bedrijfsnaam'];
                        else
                            $name = trim($orderInput['Voornaam'] . " " . $orderInput['Achternaam']);

                        $customer = new Customer();
                        $customer->setName($name);
                        $customer->setRepresentative(trim($orderInput['Voornaam'] . " " . $orderInput['Achternaam']));
                        $customer->setStreet(trim($orderInput['Straatnaam'] . " " . $orderInput['Huisnummer'] . " " . $orderInput['Huisnummer toevoeging']));
                        $customer->setZip($orderInput['Postcode']);
                        $customer->setCity($orderInput['Plaatsnaam']);
                        $customer->setCountry($orderInput['Landcode']);
                        $customer->setEmail($orderInput['Email']);
                        $customer->setPhone($orderInput['Telefoon']);
                        $customer->setPhone2($orderInput['Mobiel nummer']);
                    }
                    catch (\Exception $ex) {
                        return $this->render('AppBundle:SalesOrder:import.html.twig', array(
                            'form' => $form->createView(),
                            'success' => false,
                        ));                            
                    }

                    if ($data['partner'])
                    {
                        $customer->setPartner($data['partner']);
                        $customer->setIsPartner(Customer::HAS_PARTNER);
                    }

                    $order->setCustomer($em->getRepository('AppBundle:Customer')->checkCustomerExists($customer));

                    $em->persist($order);
                    $em->flush();

                    $order->setOrderNr($repo->generateSalesOrderNr($order));
                    $em->flush();                    
                }

                return $this->redirectToRoute("salesorder_import", array('success' => true));
            }
      
            $success = false;
        }

        return $this->render('AppBundle:SalesOrder:import.html.twig', array(
            'form' => $form->createView(),
            'success' => $success,
        ));

    }


}