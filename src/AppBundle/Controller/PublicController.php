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
 * Copiatek � info@copiatek.nl � Postbus 547 2501 CM Den Haag
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
use AppBundle\Entity\Location;
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
     * @Method({"GET"})
     */
    public function pickupAction(Request $request)
    {
        $success = null;

        $em = $this->getDoctrine()->getEntityManager();

        $allProductTypes = $em->getRepository(ProductType::class)->findAll();

        $order = new PurchaseOrder();
        $order->setOrderDate(new \DateTime());
        $order->setIsGift(true);
        $pickup = new Pickup($order);
        $supplier = new Supplier();
        $order->setSupplier($supplier);

        $form = $this->createForm(PickupForm::class, $pickup, array('productTypes' => $allProductTypes));

        return $this->render('AppBundle:Public:pickup.html.twig', array(
                'form' => $form->createView(),
                'success' => $success,
            ));
    }

    /**
     * @Route("/public/pickup/post")
     * @Method({"POST"})
     */
    public function postPickupAction(Request $request)
    {
        try
        {
            if (!$this->captchaVerify($request->request->get('g-recaptcha-response')))
            {
                //return new Response("reCaptcha is not valid", Response::HTTP_NOT_ACCEPTABLE);
            }

            $em = $this->getDoctrine()->getEntityManager();

            $allProductTypes = $em->getRepository(ProductType::class)->findAll();

            $order = new PurchaseOrder();
            $order->setOrderDate(new \DateTime());
            $order->setIsGift(true);
            $pickup = new Pickup($order);
            $supplier = new Supplier();
            $order->setSupplier($supplier);
    
            $em->persist($order);
            $em->persist($pickup);
            $em->persist($supplier);
    
            $form = $this->createForm(PickupForm::class, $pickup, array('productTypes' => $allProductTypes));

            $form->handleRequest($request); 

            if (!$form->isValid())
            {
                return new Response($form->getErrors()->current()->getMessage(), Response::HTTP_NOT_ACCEPTABLE);
            }

            #region Form data processing

            // Create full order

            $pickup->getOrder()->setSupplier($em->getRepository('AppBundle:Supplier')->checkExists($pickup->getOrder()->getSupplier()));

            $locationId = $form->get('locationId')->getData();
            $location = null;
            $zipcode = $pickup->getOrder()->getSupplier()->getZip();
            if ($locationId)
                $location = $em->getRepository(Location::class)->find($locationId);
            elseif ($zipcode)
                $location = $em->getRepository(Location::class)->findOneByZipcode($zipcode);
            if ($location) $pickup->getOrder()->setLocation($location);

            $pickup->getOrder()->setStatus($em->getRepository('AppBundle:OrderStatus')->findOrCreate($form->get('orderStatusName')->getData(), true, false));

            // Images
            $imageNames = UploadifiveController::splitFilenames($form->get('imagesNames')->getData());
            foreach ($imageNames as $k => $v)
            {
                $file = new PickupImageFile($pickup, $v, $k);
                $em->persist($file);
            }

            // Agreement
            $agreementName = $form->get('agreementName')->getData();
            if ($agreementName)
            {
                $file = new PickupAgreementFile($pickup, substr($agreementName, 13), substr($agreementName, 0, 13));
                $em->persist($file);
            }

            // Products
            $count = 0;
            foreach ($allProductTypes as $productType)
            {
                for ($i = 0; $i <= 4; $i++) 
                {
                    $productTypeName = $productType->getName();
                    $address = $form->get('address'.$i)->getData();
                    $address = $address ? 'Pickup address: ' . $address : "Address " . $i;

                    $quantity = $form->get($this->toFieldname($productTypeName, $i))->getData();
                    if ($quantity)
                    {
                        $product = new Product();
                        $product->setName($address);
                        $product->setDescription("Created by application");
                        $product->setType($productType);
                        if ($location) $product->setLocation($location);
                        $product->setSku(time() + $count);
                        $em->persist($product);

                        $r = new ProductOrderRelation($product, $pickup->getOrder());
                        $r->setQuantity($quantity);
                        $em->persist($r);

                        $count++;
                    }
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

            return new Response("Pickup added successfully", Response::HTTP_OK); 
        }
        catch (InvalidFormException $exception)
        {
            return $exception->getForm();
        }
        catch (\Exception $exception)
        {
            return new Response($exception->getMessage(), 500);
        }
    }

    // Duplicate exists in PickupForm
    private function toFieldname($productTypeName, $idx = "") {
        $productTypeName = str_replace("'", "_quote_", $productTypeName);
        $productTypeName = str_replace("/", "_slash_", $productTypeName);
        $productTypeName = str_replace(" ", "_", $productTypeName);
        $idx = $idx ? $idx : ""; // replace zero with empty
        return 'q' . $idx . $productTypeName;
    }

    /**
     * @Route("/leergeld-bestelling", name="leergeld_bestelling")
     * @Method({"GET", "POST"})
     */
    public function salesOrderOldAction(Request $request)
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

        return $this->render('AppBundle:Public:salesorder_old.html.twig', array(
                'form' => $form->createView(),
                'success' => $success,
            ));
    }

    /**
     * @Route("/public/salesorder")
     * @Method({"GET"})
     */
    public function salesOrderAction(Request $request)
    {
        return $this->render('AppBundle:Public:salesorder.html.twig');
    }

    /**
     * @Route("/public/salesorder/post")
     * @Method({"POST"})
     */
    public function postSalesOrderAction(Request $request)
    {
        try
        {
            if (!$this->captchaVerify($request->request->get('g-recaptcha-response')))
            {
                return new Response("reCaptcha is not valid", Response::HTTP_NOT_ACCEPTABLE);
            }

            $em = $this->getDoctrine()->getEntityManager();

            $order = new SalesOrder();
            $order->setOrderDate(new \DateTime());
            $order->setIsGift(false);
            $customer = new Customer();

            $em->persist($order);
            $em->persist($customer);

            $order->setCustomer($customer);

            $form = $this->createForm(PublicSalesOrderForm::class, $order);

            //$form->handleRequest($request); goes by submit as seen below
            $parameters = $request->request->all();
            $form->submit($parameters);

            if (!$order->getCustomer()->getName() || !$order->getCustomer()->getEmail())
            {
                return new Response("Customer name and email are required fields", Response::HTTP_NOT_ACCEPTABLE);
            }

            if (!$form->isValid())
            {
                return new Response($form->getErrors()->current()->getMessage(), Response::HTTP_NOT_ACCEPTABLE);
            }

            $location = $em->getReference("AppBundle:Location", $form->get('locationId')->getData());
            $order->setLocation($location);

            $order->setCustomer($em->getRepository('AppBundle:Customer')->checkExists($order->getCustomer()));
            $order->setStatus($em->getRepository('AppBundle:OrderStatus')->findOrCreate($form->get('orderStatusName')->getData(), false, true));

            $remarks = "";
            foreach ($parameters as $fld => $q)
            {
                if (substr($fld, 0, 1) == "q" && $q)
                {
                    $remarks .= ", " . substr($fld, 1). ": " . $q;
                }
            }

            if (strlen($remarks) > 2)
                $remarks = substr($remarks, 2);
            else
                return new Response("No quantities entered", Response::HTTP_NOT_ACCEPTABLE);

            $order->setRemarks($remarks);

            $em->flush();

            if (!$order->getOrderNr())
            {
                $orderNr = $em->getRepository('AppBundle:SalesOrder')->generateOrderNr($order);
                $order->setOrderNr($orderNr);
                $em->flush();
            }

            return new Response("Sales order added successfully", Response::HTTP_OK);
        }
        catch (InvalidFormException $exception)
        {
            return $exception->getForm();
        }
        catch (\Exception $exception)
        {
            return new Response($exception->getMessage(), 500);
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
            "secret"=>"6LdzW4QUAAAAAD2ys-7G0Wa7URj58VGvppOhBgDS","response"=>$recaptcha));
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) return false;

        $data = json_decode($response);

        return $data->success;
    }
}
