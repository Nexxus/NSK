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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\ProductType;
use AppBundle\Entity\Attribute;
use AppBundle\Entity\Task;
use AppBundle\Form\ProductTypeForm;

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

        $productTypes = $em->getRepository("AppBundle:ProductType")->findBy(array(), array('pindex' => 'ASC'));

        return $this->render('AppBundle:ProductType:index.html.twig',
                array('productTypes' => $productTypes));
    }

    /**
     * @Route("/edit/{id}", name="producttype_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if ($id == 0)
        {
            $productType = new ProductType();
        }
        else
        {
            /** @var ProductType */
            $productType = $em->getRepository('AppBundle:ProductType')->find($id);
        }

        $form = $this->createForm(ProductTypeForm::class, $productType);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            if ($newTaskName = $form->get('newTask')->getData())
            {
                $newTask = new Task();
                $newTask->setName($newTaskName);
                $em->persist($newTask);
                $productType->addTask($newTask);
            }

            if ($newAttributeName = $form->get('newAttribute')->getData())
            {
                $newAttributeType = $form->get('newAttributeType')->getData();
                $newAttribute = new Attribute();
                $newAttribute->setName($newAttributeName);
                $newAttribute->setAttrCode("");
                $newAttribute->setType($newAttributeType ? $newAttributeType : Attribute::TYPE_TEXT);
                $em->persist($newAttribute);
                $productType->addAttribute($newAttribute);
            }

            $em->persist($productType);
            $em->flush();

            return $this->redirectToRoute('producttype_index');
        }

        return $this->render('AppBundle:ProductType:edit.html.twig', array(
            'productType' => $productType,
            'form' => $form->createView()
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
        $em->remove($em->getReference(ProductType::class, $id));
        $em->flush();

        return $this->redirectToRoute('producttype_index');
    }
}
