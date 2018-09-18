<?php

namespace TrackBundle\Controller;

use TrackBundle\Entity\Attribute;
use TrackBundle\Entity\Product;
use TrackBundle\Entity\ProductType;
use TrackBundle\Entity\ProductAttributeRelation;
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

        // get types
        $types = $this->getDoctrine()->getRepository(ProductType::class)
            ->findAll();

        // on submit, retrieve form
        if(isset($porder)) {
            $porder = $request->get('porder');

            // create order
            echo "<pre>"; print_r($porder);echo "</pre>";

            // create products
            foreach($porder['product'] as $product) {
                $p = new Product();

                //return $this->redirectToRoute('track_index');
            }

            // create or bind to contact
        }

        return $this->render('TrackBundle:PurchaseOrder:new.html.twig', array(
            'types' => $types,
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

}
