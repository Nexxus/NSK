<?php

namespace TrackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AdminBundle\Entity\Partner;
use AdminBundle\Entity\Address;
use TrackBundle\Entity\PurchaseOrder;
use TrackBundle\Entity\Product;
use TrackBundle\Entity\ProductType;
use TrackBundle\Entity\Location;

/**
 * @Route("/track/purchaseorder")
 */
class PurchaseOrderController extends Controller
{
    /**
     * @Route("/index", name="purchaseorder_index")
     */
    public function indexAction()
    {
        $orderRepo = $this->getDoctrine()->getRepository(PurchaseOrder::class)
            ->findAll();

        return $this->render('TrackBundle:PurchaseOrder:index.html.twig', array(
            'orders' => $orderRepo,
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

        $request = Request::createFromGlobals();

        // get contacts
        $partnerRepository = $this->getDoctrine()->getRepository(Partner::class);

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
                $partner = new Partner();
                $partner->setName($con['companyname']);
                $partner->setKvkNr(0);

                // create and add address
                $address = new Address();
                $address->setType(0);
                $address->setStreet1($con['address']);
                $address->setCity($con['city']);
                $address->setCountry($con['country']);
                $address->setState($con['province']);
                $address->setZip($con['zipcode']);
                $address->setCompany($partner);

                $em->persist($address);

                $partner->addAddress($address);

                // create location
                $location = new Location();
                $location->setName($partner->getName());

                $em->persist($location);

                $partner->setLocation($location);
            } else {
                $partner = $partnerRepository->find($porder['contact']['new']);
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
                $product->setLocation($partner->getLocation());

                $em->persist($product);
                $em->flush();
            }

            // create order
            $order = new PurchaseOrder();
            $order->setLocation($partner->getLocation());
            $em->persist($order);

            $em->persist($partner);
            $em->flush();

            return $this->redirectToRoute('track_index');
        }

        return $this->render('TrackBundle:PurchaseOrder:new.html.twig', array(
            'types' => $types->findAll(),
            'partners' => $partnerRepository->findAll(),

        ));
    }

    /**
     * @Route("/edit", name="purchaseorder_edit")
     */
    public function editAction()
    {
        return $this->render('TrackBundle:PurchaseOrder:edit.html.twig', array(
            // ...
        ));
    }

    /**
     * @Route("/delete", name="purchaseorder_delete")
     */
    public function deleteAction()
    {
        return $this->render('TrackBundle:PurchaseOrder:delete.html.twig', array(
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

        return $this->render('TrackBundle:PurchaseOrder:inlist.html.twig', array(
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
                    . ' FROM TrackBundle:Product p'
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
