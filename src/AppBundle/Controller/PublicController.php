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
 * Copiatek – info@copiatek.nl – Postbus 547 2501 CM Den Haag
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Pickup;
use AppBundle\Entity\Product;
use AppBundle\Entity\ProductType;
use AppBundle\Entity\PurchaseOrder;
use AppBundle\Entity\SalesOrder;
use AppBundle\Entity\Supplier;
use AppBundle\Entity\Customer;
use AppBundle\Entity\PickupImageFile;
use AppBundle\Entity\PickupAgreementFile;
use AppBundle\Entity\ProductOrderRelation;
use AppBundle\Form\PickupForm;
use AppBundle\Form\PublicSalesOrderForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class PublicController extends Controller
{
    /**
     * @Route("/public/pickup", name="pickup")
     * @Route("/ophaaldienst", name="ophaaldienst")
     * @Method({"GET", "POST"})
     */
    public function pickupAction(Request $request)
    {
        $success = null;

        $em = $this->getDoctrine()->getEntityManager();

        $allProductTypes = $em->getRepository(ProductType::class)->findAll();

        $pickup = new Pickup();
        $order = new PurchaseOrder();
        $order->setOrderDate(new \DateTime());
        $order->setIsGift(true);
        $supplier = new Supplier();

        $em->persist($pickup);
        $em->persist($order);
        $em->persist($supplier);

        $order->setSupplier($supplier);
        $pickup->setOrder($order);

        $form = $this->createForm(PickupForm::class, $pickup, array('productTypes' => $allProductTypes));

        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $this->captchaVerify($request->request->get('g-recaptcha-response')))
        {
            if ($form->isValid())
            {
                try
                {
                    #region Form data processing

                    // Create full order

                    $location = $em->getReference("AppBundle:Location", $form->get('locationId')->getData());
                    $pickup->getOrder()->setLocation($location);

                    $pickup->getOrder()->setSupplier($em->getRepository('AppBundle:Supplier')->checkExists($pickup->getOrder()->getSupplier()));

                    $pickup->getOrder()->setStatus($em->getRepository('AppBundle:OrderStatus')->findOrCreate($form->get('orderStatusName')->getData(), true, false));

                    // Images
                    $imageNames = UploadifiveController::splitFilenames($form->get('imagesNames')->getData());
                    foreach ($imageNames as $k => $v)
                    {
                        $file = new PickupImageFile();
                        $file->setUniqueServerFilename($k);
                        $file->setOriginalClientFilename($v);
                        $em->persist($file);
                        $pickup->addImage($file);
                    }

                    // Agreement
                    $agreementName = $form->get('agreementName')->getData();
                    if ($agreementName)
                    {
                        $file = new PickupAgreementFile();
                        $file->setUniqueServerFilename(substr($agreementName, 0, 13));
                        $file->setOriginalClientFilename(substr($agreementName, 13));
                        $em->persist($file);
                        $pickup->setAgreement($file);
                    }

                    // Products
                    $count = 0;
                    foreach ($allProductTypes as $productType)
                    {
                        $productTypeName = $productType->getName();
                        $quantity = $form->get('q'.$productTypeName)->getData();
                        if ($quantity)
                        {
                            $product = new Product();
                            $product->setName($productTypeName);
                            $product->setDescription("Created by application");
                            $product->setType($productType);
                            $product->setLocation($location);
                            $product->setSku(time() + $count);
                            $em->persist($product);

                            $r = new ProductOrderRelation();
                            $r->setOrder($pickup->getOrder());
                            $r->setProduct($product);
                            $r->setQuantity($quantity);
                            $em->persist($r);

                            $pickup->getOrder()->addProductRelation($r);

                            $count++;
                        }
                    }

                    #endregion

                    $em->flush();

                    if (!$pickup->getOrder()->getOrderNr())
                    {
                        $orderNr = $em->getRepository('AppBundle:PurchaseOrder')->generateOrderNr($pickup->getOrder());
                        $pickup->getOrder()->setOrderNr($orderNr);
                        $em->flush();
                    }

                    $success = true;
                }
                catch (\Exception $ex)
                {
                    $success = false;
                }
            }
            else
            {
                $success = false;
            }
        }

        return $this->render('AppBundle:Public:pickup.html.twig', array(
                'form' => $form->createView(),
                'success' => $success,
            ));
    }

    /**
     * @Route("/public/salesorder", name="public_salesorder")
     * @Route("/leergeld-bestelling", name="leergeld_bestelling")
     * @Method({"GET", "POST"})
     */
    public function salesOrderAction(Request $request)
    {
        $success = null;

        $em = $this->getDoctrine()->getEntityManager();

        $order = new SalesOrder();
        $order->setOrderDate(new \DateTime());
        $order->setIsGift(false);
        $customer = new Customer();

        $em->persist($order);
        $em->persist($customer);

        $order->setCustomer($customer);

        $form = $this->createForm(PublicSalesOrderForm::class, $order);

        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $this->captchaVerify($request->request->get('g-recaptcha-response')))
        {
            if ($form->isValid())
            {
                try
                {
                    $location = $em->getReference("AppBundle:Location", $form->get('locationId')->getData());
                    $order->setLocation($location);

                    $order->setCustomer($em->getRepository('AppBundle:Customer')->checkExists($order->getCustomer()));
                    $order->setStatus($em->getRepository('AppBundle:OrderStatus')->findOrCreate($form->get('orderStatusName')->getData(), false, true));

                    $remarks = "";
                    foreach ($request->request->all()["public_sales_order_form"] as $fld => $q)
                    {
                        if (substr($fld, 0, 1) == "q" && $q)
                        {
                            $remarks .= ", " . substr($fld, 1). ": " . $q;
                        }
                    }

                    if (strlen($remarks) > 2)
                        $remarks = substr($remarks, 2);
                    else
                        $remarks = "No quantities entered...";

                    $order->setRemarks($remarks);

                    $em->flush();

                    if (!$order->getOrderNr())
                    {
                        $orderNr = $em->getRepository('AppBundle:SalesOrder')->generateOrderNr($order);
                        $order->setOrderNr($orderNr);
                        $em->flush();
                    }

                    $success = true;
                }
                catch (\Exception $ex)
                {
                    $success = false;
                }
            }
            else
            {
                $success = false;
            }
        }

             return $this->render('AppBundle:Public:salesorder.html.twig', array(
                'form' => $form->createView(),
                'success' => $success,
            ));
    }

     /**
     * @Route("/public/salesorderexample")
     * @Method({"GET"})
     */
    public function salesOrderHtmlAction(Request $request)
    {
        return $this->render('AppBundle:Public:salesorderexample.html.twig');
    }

    /**
     * @Route("/public/api/salesorder")
     * @Method({"POST"})
     */
    public function postSalesOrderAction(Request $request)
    {
        try {

            $em = $this->getDoctrine()->getEntityManager();

            $order = new SalesOrder();
            $order->setOrderDate(new \DateTime());
            $order->setIsGift(false);
            $customer = new Customer();

            $em->persist($order);
            $em->persist($customer);

            $order->setCustomer($customer);

            $form = $this->createForm(PublicSalesOrderForm::class, $order, ['csrf_protection' => false]);
            $parameters = $request->request->all();
            $form->submit($parameters);

            if ($form->isValid())
            {
                return new Response("Sales order added successfully", Response::HTTP_OK);
            }
            else {
                return new Response($form->getErrors()->current()->getMessage(), Response::HTTP_NOT_ACCEPTABLE);
            }
        }
        catch (InvalidFormException $exception) {

            return $exception->getForm();

        }
    }

    private function captchaVerify($recaptcha)
    {
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            "secret"=>"6LdyzXMUAAAAAHSGkIAZE1QirknxwARCQbgAfHm4","response"=>$recaptcha));
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);

        return $data->success;
    }
}
