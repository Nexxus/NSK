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
use AppBundle\Form\PublicOrderForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Repository\SupplierRepository;

class PublicController extends Controller
{
    /**
     * @Route("/public/pickuptest")
     * @Method({"GET"})
     */
    public function pickupTestAction(Request $request)
    {
        return $this->render('AppBundle:Public:pickuptest.html.twig');
    }

    /**
     * @Route("/public/pickup", name="pickup")
     * @Method({"GET"})
     */
    public function pickupAction(Request $request)
    {
        if ($this->container->has('profiler'))
        {
            $this->container->get('profiler')->disable();
        }

        $recaptchaKey = $request->get("recaptchaKey");
        $locationId = $request->get("locationId");
        $orderStatusName = $request->get("orderStatusName");
        $maxAddresses = $request->get("maxAddresses");
        $origin = $request->get("origin");
        $confirmPage = $request->get("confirmPage");

        $em = $this->getDoctrine()->getManager();

        $allProductTypes = $em->getRepository(ProductType::class)->findAll();

        $order = new PurchaseOrder();
        $order->setOrderDate(new \DateTime());
        $order->setIsGift(true);
        $pickup = new Pickup($order);
        $pickup->setOrigin($origin);
        $supplier = new Supplier();
        $order->setSupplier($supplier);

        $form = $this->createForm(PickupForm::class, $pickup, array(
            'productTypes' => $allProductTypes, 
            'maxAddresses' => $maxAddresses,
            'locationId' => $locationId,
            'confirmPage' => $confirmPage,
            'orderStatusName' => $orderStatusName));

        $response = $this->render('AppBundle:Public:pickup.html.twig', array(
                'form' => $form->createView(),
                'success' => null,
                'allProductTypes' => $allProductTypes,
                'recaptchaKey' => $recaptchaKey,
                'maxAddresses' => $maxAddresses,
            ));

        return $response;
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
                return new Response("reCaptcha is not valid", Response::HTTP_NOT_ACCEPTABLE);
            }

            $locationId = $request->request->get("pickup_form")['locationId'];
            $orderStatusName = $request->request->get("pickup_form")['orderStatusName'];
            $maxAddresses = $request->request->get("pickup_form")['maxAddresses'];
            $confirmPage = $request->request->get("pickup_form")['confirmPage'];

            $em = $this->getDoctrine()->getManager();

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
    
            $form = $this->createForm(PickupForm::class, $pickup, array(
                'productTypes' => $allProductTypes, 
                'maxAddresses' => $maxAddresses,
                'locationId' => $locationId,
                'confirmPage' => $confirmPage,
                'orderStatusName' => $orderStatusName));

            $form->handleRequest($request); 

            if (!$form->isValid())
            {
                return new Response($form->getErrors()->current()->getMessage(), Response::HTTP_NOT_ACCEPTABLE);
            }

            #region Form data processing

            $pickup->getOrder()->setSupplier($em->getRepository('AppBundle:Supplier')->checkSupplierExists($pickup->getOrder()->getSupplier()));
            $pickup->getOrder()->setStatus($em->getRepository('AppBundle:OrderStatus')->findOrCreate($orderStatusName, true, false));

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
            $countAddresses = $form->get('countAddresses')->getData();
            if (!$countAddresses) $countAddresses = 1;
            foreach ($allProductTypes as $productType)
            {
                for ($i = 1; $i <= $countAddresses; $i++) 
                {
                    $quantity = $form->get('quantity_' . $i . '_' . $productType->getId())->getData();

                    if ($quantity)
                    {
                        $address = $form->get('address'.$i)->getData() . ", " .
                            trim($form->get('address_zip_'.$i)->getData() . " " . $form->get('address_city_'.$i)->getData());

                        if ($address == ", " && $i == 1) {
                            $address = $pickup->getOrder()->getSupplier()->getAddressString(false);
                        }
                        else if ($address == ", ") {
                            $address = "Address " . $i;
                        }
                        else {
                            $address = 'Pickup address: ' . $address;
                        }

                        $product = new Product();
                        $product->setName($address);
                        $product->setDescription("Created by application");
                        $product->setType($productType);
                        $product->setLocation($em->getRepository(Location::class)->find($locationId));
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
                $orderNr = $em->getRepository('AppBundle:PurchaseOrder')->generatePurchaseOrderNr($pickup->getOrder());
                $pickup->getOrder()->setOrderNr($orderNr);
                $em->flush();
            }

            $this->sendStatusMail($order);

            $message = $confirmPage ? $confirmPage : "Pickup added successfully";

            return new Response($message, Response::HTTP_OK); 
        }
        catch (\Exception $exception)
        {
            return new Response($exception->getMessage(), 500);
        }
    }

    /**
     * @Route("/public/ordertest")
     * @Method({"GET"})
     */
    public function orderTestAction(Request $request)
    {
        return $this->render('AppBundle:Public:ordertest.html.twig');
    }

    /**
     * @Route("/public/order")
     * @Method({"GET"})
     */
    public function orderAction(Request $request)
    {
        if ($this->container->has('profiler'))
        {
            $this->container->get('profiler')->disable();
        }

        $recaptchaKey = $request->get("recaptchaKey");
        $locationId = $request->get("locationId");
        $confirmPage = $request->get("confirmPage");
        $orderStatusName = $request->get("orderStatusName");
        $products = $request->get("products");

        $em = $this->getDoctrine()->getManager();

        $order = new SalesOrder();
        $order->setOrderDate(new \DateTime());
        $order->setIsGift(false);
        $customer = new Customer();

        $em->persist($order);
        $em->persist($customer);

        $order->setCustomer($customer);

        $form = $this->createForm(PublicOrderForm::class, $order, array(
            'products' => $products, 
            'locationId' => $locationId,
            'confirmPage' => $confirmPage,
            'orderStatusName' => $orderStatusName));

        $response = $this->render('AppBundle:Public:order.html.twig', array(
                'form' => $form->createView(),
                'success' => null,
                'recaptchaKey' => $recaptchaKey
            ));

        return $response;
    }

    /**
     * @Route("/public/order/post")
     * @Method({"POST"})
     */
    public function postOrderAction(Request $request)
    {
        try
        {
            if (!$this->captchaVerify($request->request->get('g-recaptcha-response')))
            {
                return new Response("reCaptcha is not valid", Response::HTTP_NOT_ACCEPTABLE);
            }

            $locationId = $request->request->get("public_order_form")['locationId'];
            $orderStatusName = $request->request->get("public_order_form")['orderStatusName'];
            $confirmPage = $request->request->get("public_order_form")['confirmPage'];

            $em = $this->getDoctrine()->getManager();

            $order = new SalesOrder();
            $order->setOrderDate(new \DateTime());
            $order->setIsGift(false);
            $customer = new Customer();
            $order->setCustomer($customer);

            $em->persist($order);
            $em->persist($customer);

            $form = $this->createForm(PublicOrderForm::class, $order, array(
                'locationId' => $locationId,
                'confirmPage' => $confirmPage,
                'orderStatusName' => $orderStatusName));

            $form->handleRequest($request); 

            if (!$form->isValid())
            {
                return new Response($form->getErrors()->current()->getMessage(), Response::HTTP_NOT_ACCEPTABLE);
            }

            #region Form data processing

            $order->setCustomer($em->getRepository('AppBundle:Customer')->checkCustomerExists($order->getCustomer()));
            $order->setStatus($em->getRepository('AppBundle:OrderStatus')->findOrCreate($orderStatusName, false, true));

            $remarks = "";
            $products = $form->get('products')->getData();
            foreach ($products as $product)
            {
                if ($product['quantity'] > 0)
                {
                    $remarks .= $product['name'] . ": " . $product['quantity'] . "x\r\n";
                }
            }

            if (strlen($remarks) < 4)
                $remarks = "No quantities entered...";

            $order->setRemarks($remarks);

            #endregion

            $em->flush();

            if (!$order->getOrderNr())
            {
                $orderNr = $em->getRepository('AppBundle:SalesOrder')->generateSalesOrderNr($order);
                $order->setOrderNr($orderNr);
                $em->flush();
            }

            $message = $confirmPage ? $confirmPage : "Sales order added successfully";

            return new Response($message, Response::HTTP_OK);
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

    private function sendStatusMail(PurchaseOrder $order)
    {
        $body = $order->getStatus()->getMailbody();
        $to = $order->getSupplier()->getEmail();
        //$to = "ronald.debakker@copiatek.nl";

        if (!$to || !$body)
            return;       

        $body = str_replace("%supplier.name%", $order->getSupplier()->getName() ?? "leverancier", $body);
        $body = str_replace("%order.nr%", $order->getOrderNr(), $body);

        if ($order->getPickup() && $order->getPickup()->getRealPickupDate())
        {
            $body = str_replace("%pickup.datetime%", $order->getPickup()->getRealPickupDate()->format("j-n-Y G:i"), $body);
            $body = str_replace("%pickup.date%", $order->getPickup()->getRealPickupDate()->format("j-n-Y"), $body);
        }        
        elseif ($order->getPickup() && $order->getPickup()->getPickupDate())
        {
            $body = str_replace("%pickup.datetime%", $order->getPickup()->getPickupDate()->format("j-n-Y G:i"), $body);
            $body = str_replace("%pickup.date%", $order->getPickup()->getPickupDate()->format("j-n-Y"), $body);
        }

        $message = (new \Swift_Message('Uw opdracht is ontvangen en zal worden ingepland'))
            ->setFrom('logistiek@copiatek.nl')
            ->setTo($to)
            ->setBody($body, 'text/plain');

        /** @var \Swift_Mailer */
        $mailer = $this->get('mailer');
        $mailer->send($message);
    } 
}
