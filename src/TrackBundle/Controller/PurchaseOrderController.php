<?php

namespace TrackBundle\Controller;

use TrackBundle\Entity\Attribute;
use TrackBundle\Entity\Product;
use TrackBundle\Entity\ProductType;
use TrackBundle\Entity\ProductAttributeRelation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        // get types
        $types = []; 
        $tRepo = $this->getDoctrine()->getRepository(ProductType::class)
            ->findAll();

        foreach($tRepo as $type) 
        {
            $types[] = $type->getName();
        }

        $pOrderData = ['msg' => 'Create a Purchase Order'];

        $form = $this->createFormBuilder($pOrderData)
                    ->add('type', ChoiceType::class, [
                        'mapped' => false,
                    ])
                    ->getForm();

        return $this->render('TrackBundle:PurchaseOrder:new.html.twig', array(
            'types' => $types,
            'form' => $form->createView(),
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
