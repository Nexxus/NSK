<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Supplier;
use AppBundle\Entity\Address;
use AppBundle\Entity\PurchaseOrder;
use AppBundle\Entity\Product;
use AppBundle\Entity\ProductType;
use AppBundle\Entity\Location;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use AppBundle\Form\IndexSearchType;

/**
 * @Route("/track/purchaseorder")
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

        $form = $this->createForm(IndexSearchType::class, array());

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
     * @Route("/new", name="purchaseorder_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $porder = $request->get('porder');

        $location = $this->get('security.token_storage')->getToken()->getUser()->getLocation();

        // get contacts
        $supplierRepository = $this->getDoctrine()->getRepository(Supplier::class);

        // get types
        $types = $this->getDoctrine()->getRepository(ProductType::class);

        // on submit, retrieve form
        if(isset($porder))
        {
            $porder = $request->get('porder');

            // new contact, create it
            if($porder['contact']['new']=='new')
            {
                $con = $porder['contact'];
                $supplier = new Supplier();
                $supplier->setName($con['companyname']);
                $supplier->setKvkNr(0);

                // create and add address
                $address = new Address();
                $address->setType(0);
                $address->setStreet1($con['address']);
                $address->setCity($con['city']);
                $address->setCountry($con['country']);
                $address->setState($con['province']);
                $address->setZip($con['zipcode']);
                $address->setCompany($supplier);

                $em->persist($address);
                $em->persist($supplier);

                $supplier->addAddress($address);
                $supplier->setLocation($location);
            } else {
                $supplier = $supplierRepository->find($porder['contact']['new']);
            }

            // create products
            foreach($porder['product'] as $p)
            {
                $product = new Product();

                // generate sku
                $type = $em->getRepository(ProductType::class)
                    ->find($p['type']);

                $product->setType($type);
                $product = $this->generateNewSku($product);

                if($p['comments']!='')
                {
                    $product->setName($p['comments']);
                } else {
                    $product->setName($product->getType()->getName());
                }
                $product->setQuantity($p['quantity']);
                $product->setLocation($location);

                $em->persist($product);
                $em->flush();
            }

            // create order
            $order = new PurchaseOrder();
            $order->setLocation($location);
            $order->setSupplier($supplier);

            $em->persist($order);

            $em->persist($supplier);
            $em->flush();

            return $this->redirectToRoute('purchaseorder_index');
        }

        return $this->render('AppBundle:PurchaseOrder:new.html.twig', array(
            'types' => $types->findAll(),
            'suppliers' => $supplierRepository->findAll(),

        ));
    }

    /**
     * @Route("/edit", name="purchaseorder_edit")
     */
    public function editAction()
    {
        return $this->render('AppBundle:PurchaseOrder:edit.html.twig', array(
            // ...
        ));
    }

    /**
     * @Route("/delete", name="purchaseorder_delete")
     */
    public function deleteAction()
    {
        return $this->render('AppBundle:PurchaseOrder:delete.html.twig', array(
            // ...
        ));
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

    /*
     * Returns true if a SKU in the database is free
     */
    public function checkFreeSku($sku) {
        $em = $this->getDoctrine()->getManager();

        $skuquery = $em->createQuery(
                    'SELECT p.sku'
                    . ' FROM AppBundle:Product p'
                    . ' WHERE p.sku = :givensku')
                    ->setParameter('givensku', $sku);
        $result = $skuquery->getResult();

        return (count($result) == 0);
    }

    /*
     * Generates new SKU, avoids duplicates
     */
    public function generateNewSku(Product $product)
    {
        $num  = 0;
        $gsku = "";

        // if type is set, add prefix
        if ($product->getType())
        {
            $gsku = substr($product->getType()->getName(), 0, 1);
        }

        // increment if taken
        $free = $this->checkFreeSku($gsku);
        while(!$free) {
            $num++;
            $gsku = substr($product->getType(), 0, 1) . $num;
            $free = $this->checkFreeSku($gsku);
        }

        $product->setSku($gsku);
        return $product;
    }
}
