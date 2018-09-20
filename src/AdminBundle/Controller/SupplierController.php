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

namespace AdminBundle\Controller;

use AdminBundle\Entity\Supplier;
use AdminBundle\Form\SupplierType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


/**
 * @Route("/admin/supplier")
 */
class SupplierController extends Controller
{
    /**
     * @Route("/", name="supplier_index")
     */
    public function indexAction()
    {
        $repo = $this->getDoctrine()->getRepository('AdminBundle:Supplier');
        $suppliers = $repo->findAll();

        return $this->render('AdminBundle:Supplier:index.html.twig', array(
            'suppliers' => $suppliers));
    }

    /**
     * @Route("/edit/{id}", name="supplier_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if ($id == 0)
        {
            $supplier = new Supplier();
        }
        else
        {
            $supplier = $em->getRepository('AdminBundle:Supplier')->find($id);
        }

        $form = $this->createForm(SupplierType::class, $supplier);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($supplier);
            $em->flush();

            return $this->redirectToRoute('supplier_index');
        }


        return $this->render('AdminBundle:Supplier:edit.html.twig', array(
                'form' => $form->createView(),
            ));
    }

    /**
     * @Route("/delete/{id}", name="supplier_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $supplier = $em->getRepository('AdminBundle:Supplier')->find($id);
        $em->remove($supplier);
        $em->flush();

        return $this->redirectToRoute('supplier_index');
    }
}
