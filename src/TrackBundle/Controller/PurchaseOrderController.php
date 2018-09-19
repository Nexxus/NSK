<?php

namespace TrackBundle\Controller;

use TrackBundle\Entity\Attribute;
use TrackBundle\Entity\Product;
use TrackBundle\Entity\ProductType;
use TrackBundle\Entity\ProductAttributeRelation;
use TrackBundle\Entity\Location;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use AdminBundle\Entity\Partner;
use AdminBundle\Entity\Address;

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
        return $this->render('TrackBundle:PurchaseOrder:index.html.twig', array(
            // ...
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
        $contacts = $this->getDoctrine()->getRepository(Partner::class)
            ->findAll();

        // get types
        $types = $this->getDoctrine()->getRepository(ProductType::class)
            ->findAll();

        // on submit, retrieve form
        if(isset($porder)) 
        {
            $porder = $request->get('porder');

            // new contact, create it
            if($porder['contact']['new']=='new') 
            {
                $con = $porder['contact'];
                $company = new Partner();
                $company->setName($con['companyname']);
                $company->setKvkNr(0);

                // create and add address
                $address = new Address();
                $address->setType(0);
                $address->setStreet1($con['address']);
                $address->setCity($con['city']);
                $address->setCountry($con['country']);
                $address->setState($con['province']);
                $address->setZip($con['zipcode']);
                $address->setCompany($company);

                $company->addAddress($address);

                // create location
                $location = new Location();
                $location->setName($company->getName());
            }

            // create order
            echo "<pre>"; print_r($porder);echo "</pre>";

            // access ProductController for methods
            // $productController = $this->get('productController');

            // create products
            foreach($porder['product'] as $p)
            {
                $product = new Product();

                // generate sku
                $type = $em->getRepository(ProductType::class)
                    ->find($p['type']);

                $product->setType($type);
                $product = $this->generateNewSku($product);
                $product->setName($p['comments']);
                $product->setQuantity($p['quantity']);
                $product->setLocation($location);

                $em->persist($product);
            }

            $em->persist($company);
            $em->persist($address);
            $em->persist($location);
            $em->flush();

            return $this->redirectToRoute('track_index');
        }

        return $this->render('TrackBundle:PurchaseOrder:new.html.twig', array(
            'types' => $types,
            'partners' => $partners,
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
            $gsku = substr($product->getType(), 0, 1) . $gsku;
            echo "sku is " . $gsku;
        } 

        // increment if taken
        $free = $this->checkFreeSku($gsku);
        while(!$free) {
            $num++;
            $gsku = substr($product->getType(), 0, 1) . $num;
            $free = $this->checkFreeSku($gsku);
        }

        echo "sku" . $gsku;

        $product->setSku($gsku);
        return $product;
    }
}
