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

use AppBundle\Entity\Customer;
use AppBundle\Form\CustomerForm;
use AppBundle\Form\IndexSearchForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\FormError;

/**
 * @Route("/customer")
 */
class CustomerController extends Controller
{
    /**
     * @Route("/", name="customer_index")
     */
    public function indexAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Customer');

        $customers = array();

        $form = $this->createForm(IndexSearchForm::class, array());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form['query']->getData())
        {
            $data = $form->getData();
            $customers = $repo->findBySearchQuery($data['query']);
        }
        else
        {
            $customers = $repo->findMine($this->getUser());
        }

        $paginator = $this->get('knp_paginator');
        $customersPage = $paginator->paginate($customers, $request->query->getInt('page', 1), 10);

        return $this->render('AppBundle:Customer:index.html.twig', array(
            'customers' => $customersPage,
            'form' => $form->createView()
            ));
    }

    /**
     * @Route("/edit/{id}/{success}", name="customer_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, $id = 0, $success = null)
    {
        $em = $this->getDoctrine()->getManager();

        if ($id == 0)
        {
            $customer = new Customer();
        }
        else
        {
            $customer = $em->getRepository('AppBundle:Customer')->find($id);
        }

        $form = $this->createForm(CustomerForm::class, $customer, array('user' => $this->getUser()));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($customer);
            $em->flush();

            return $this->redirectToRoute("customer_edit", array('id' => $customer->getId(), 'success' => true));
        }
        else if ($form->isSubmitted())
        {
            $success = false;
        }

        return $this->render('AppBundle:Customer:edit.html.twig', array(
                'customer' => $customer,
                'form' => $form->createView(),
                'success' => $success,
            ));
    }

    /**
     * @Route("/delete/{id}", name="customer_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $customer = $em->getRepository('AppBundle:Customer')->find($id);
        $em->remove($customer);
        $em->flush();

        return $this->redirectToRoute('customer_index');
    }
}
