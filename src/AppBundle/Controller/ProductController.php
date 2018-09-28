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
use AppBundle\Form\ProductType;
use AppBundle\Form\IndexSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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

        $form = $this->createForm(IndexSearchType::class, array());

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
     * @Route("/edit/{id}", name="product_edit")
     */
    public function editAction(Request $request, $id)
    {
        $success = null;

        $em = $this->getDoctrine()->getManager();

        /** @var \AppBundle\Repository\ProductRepository */
        $repo = $em->getRepository('AppBundle:Product');

        if ($id == 0)
        {
            $product = new Product();
        }
        else
        {
            /** @var Product */
            $product = $repo->find($id);
        }

        $repo->generateAttributeRelations($product);

        $form = $this->createForm(ProductType::class, $product);

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

        return $this->render('AppBundle:Product:edit.html.twig', array(
                'product' => $product,
                'form' => $form->createView(),
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
