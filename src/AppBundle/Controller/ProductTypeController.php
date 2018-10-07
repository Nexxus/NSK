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


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Doctrine\ORM\EntityManagerInterface;

use AppBundle\Entity\ProductType;
use AppBundle\Entity\ProductTypeAttribute;
use AppBundle\Entity\Attribute;

/**
 * @Route("admin/type")
 */
class ProductTypeController extends Controller
{
    /**
     * @Route("/", name="producttype_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $products = $em->createQuery(
                    "SELECT pt.id, pt.name, pt.pindex, COUNT(p.id) AS productcount FROM AppBundle:ProductType pt
                        LEFT JOIN AppBundle:Product p WITH p.type=pt.id
                        GROUP BY pt.id")
                ->getResult();

        return $this->render('AppBundle:Type:index.html.twig',
                array('products' => $products));
    }

    /**
     * @Route("/create", name="producttype_new")
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                "SELECT COUNT(pt.id) "
                . "FROM AppBundle:ProductType pt")
                ->getResult();

        $index = $query[0][1]+1;

        $producttype = new ProductType();
        $producttype->setPindex($index);

        $form = $this->createFormBuilder($producttype)
                    ->add('name', TextType::class)
                    ->add('pindex', IntegerType::class)
                                ->add('isAttribute',  CheckboxType::class, [
                            'label' => 'Products of this type can be an attribute (a part) of another product',
                            'required' => false
                        ])
                    ->add('comment', TextType::class, array('required' => false))
                    ->add('save', SubmitType::class, array('label' => 'Create Type'))
                    ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $producttype = $form->getData();

            $em->persist($producttype);
            $em->flush();

            return $this->redirectToRoute('producttype_index', ["order" => "Product Type has been saved."]);
        }
        else
        {
            return $this->render('AppBundle:Type:new.html.twig', array(
                'form' => $form->createView(),
            ));
        }
    }

    /**
     * @Route("/show/{id}", name="producttype_show")
     * @Method("GET")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine();

        // get product
        $producttype = $em->getRepository('AppBundle:ProductType')
                ->find($id);

        // get all attributes with correct order
        $query = $em->getRepository('AppBundle:Attribute')
                ->createQueryBuilder('a')
                ->orderBy('a.id', 'ASC')
                ->getQuery();

        $attributes = $query->getResult();

        return $this->render('AppBundle:Type:show.html.twig', array(
            'producttype' => $producttype,
            'attributes' => $attributes,
        ));
    }

    /**
     * @Route("/edit/{id}", name="producttype_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(ProductType $producttype, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        // build basic form
        $editForm = $this->createFormBuilder($producttype)
                ->add('name', TextType::class)
                ->add('isAttribute',  CheckboxType::class, [
                            'label' => 'Products of this type can be an attribute (a part) of another product',
                            'required' => false
                        ])
                ->add('save', SubmitType::class)
                ->getForm();
        $editForm->handleRequest($request);

        // get all attributes
        $attributes = $em->getRepository('AppBundle:Attribute')->findAll();

        $choices = [];
        foreach($attributes as $attr) {
            $choices[$attr->getName()] = $attr->getId();
        }

        $attrForm = $this->createFormBuilder()
                ->add('attribute', ChoiceType::class, [
                    'choices' => $choices
                ])
                ->add('save', SubmitType::class)
                ->getForm();
        $attrForm->handleRequest($request);

        // product edited
        if($editForm->isSubmitted() && $editForm->isValid()) {
            $em->persist($producttype);
            $em->flush();

            return $this->redirectToRoute('producttype_index');
        }

        // attribute added
        if($attrForm->isSubmitted()) {
            $attr = $attrForm->getData();

            $this->addAttributeToOneType($producttype->getId(), $attr['attribute']);
        }

        // create attr name list (not quite done)
        /*
        $attrList = [];
        foreach($attributes as $attr) {
            $attrList[$attr->getName()] = $attr->getId();
        }

        // foreach type attribute, show dropdown option
        foreach($typeattributes as $tAttr) {
            $editForm = $editForm->add('attribute_'.$tAttr->getId(), ChoiceType::class,[
                'choices'   => $attrList,
                'mapped'    => false,
                ]
            );
        }*/

        // put attributes in array
        $attributeCollection = $producttype->getAttributes();
        $attrList = [];
        for($i=0;$i<$attributeCollection->count();$i++) {
           $attrList[] = $attributeCollection->get($i);
        }

        return $this->render('AppBundle:Type:edit.html.twig', array(
            'form' => $editForm->createView(),
            'producttype' => $producttype,
            'attrform' => $attrForm->createView(),
            'attrlist' => $attrList,
        ));
    }

    /**
     * Delete producttype, make sure no products are assigned to it
     *
     * @Route("/delete/{id}", name="producttype_delete")
     * @Method("GET")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        // check if any products have the type
        $producttype = $em->getRepository('AppBundle:ProductType')
                ->find($id);

        // delete the type
        $em->remove($producttype);
        $em->flush();

        return $this->redirectToRoute('producttype_index');
    }
    /**
     * Bind attribute to a product type.
     *
     * @param type $typeid
     * @param type $attrid
     */
    public function addAttributeToOneType($typeid, $attrid)
    {
        $em = $this->getDoctrine()->getManager();

        $producttype = $em->getRepository('AppBundle:ProductType')
                ->find($typeid);

        $attribute = $em->getRepository('AppBundle:Attribute')
                ->find($attrid);

        $producttype->addAttribute($attribute);
        $em->persist($producttype);
        $em->flush();
    }

    /**
     * Delete producttype, make sure no products are assigned to it
     *
     * @Route("/removeAttribute/{type}/{attr}", name="producttype_removeAttribute")
     * @Method("GET")
     */
    public function removeAttributeOfType(ProductType $type, Attribute $attr)
    {
        $em = $this->getDoctrine()->getManager();

        $producttype = $em->getRepository('AppBundle:ProductType')
                ->find($type);

        $producttype->removeAttribute($attr);
        $em->persist($producttype);
        $em->flush();

        return $this->redirectToRoute('producttype_edit', [
            'id' => $type->getId()
        ]);
    }
}
