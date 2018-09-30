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

use AppBundle\Entity\Product;
use AppBundle\Entity\ProductType;
use AppBundle\Entity\ProductOrderRelation;
use AppBundle\Entity\PurchaseOrder;
use AppBundle\Entity\AOrder;
use AppBundle\Form\ProductForm;
use AppBundle\Form\IndexSearchForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/product")
 */
class ProductController extends Controller
{
    /**
     * @Route("/", name="product_index")
     */
    public function indexAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Product');

        $products = array();

        $form = $this->createForm(IndexSearchForm::class, array());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $data = $form->getData();
            $products = $repo->findBySearchQuery($data['query']);
        }
        else
        {
            $products = $repo->findAll();
        }

        $paginator = $this->get('knp_paginator');
        $productsPage = $paginator->paginate($products, $request->query->getInt('page', 1), 10);

        return $this->render('AppBundle:Product:index.html.twig', array(
            'products' => $productsPage,
            'form' => $form->createView()
            ));
    }

    /**
     * @Route("/list/{orderId}", name="product_list")
     */
    public function listAction(Request $request, $orderId)
    {
        /** @var AOrder */
        $order = $this->getDoctrine()->getEntityManager()->find(AOrder::class, $orderId);

        return $this->render('AppBundle:Product:list.ajax.twig', array(
            'productRelations' => $order->getProductRelations()
            ));
    }

    /**
     * @Route("/edit/{id}/{purchaseOrderId}/{productTypeId}", name="product_edit")
     */
    public function editAction(Request $request, $id = 0, $purchaseOrderId = 0, $productTypeId = 0)
    {
        $success = null;

        $em = $this->getDoctrine()->getManager();

        /** @var \AppBundle\Repository\ProductRepository */
        $repo = $em->getRepository('AppBundle:Product');

        if ($id == 0)
        {
            $product = new Product();
            $product->setQuantity(1);
        }
        else
        {
            /** @var Product */
            $product = $repo->find($id);
        }

        if ($productTypeId > 0)
        {
            $product->setType($em->getReference(ProductType::class, $productTypeId));
        }

        if ($purchaseOrderId > 0)
        {
            $repo->generateOrderRelation($product, $em->find(PurchaseOrder::class, $purchaseOrderId));
        }

        $repo->generateAttributeRelations($product);

        $form = $this->createForm(ProductForm::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($product);
            $em->flush();
            $success = true;
        }
        else if ($form->isSubmitted())
        {
            $success = false;
        }

        return $this->render('AppBundle:Product:edit.ajax.twig', array(
                'product' => $product,
                'form' => $form->createView(),
                'formAction' => $request->getRequestUri(),
                'success' => $success,
            ));
    }

    /**
     * @Route("/delete/{id}", name="product_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('AppBundle:Product')->find($id);
        $em->remove($product);
        $em->flush();

        return $this->redirectToRoute('product_index');
    }
}
