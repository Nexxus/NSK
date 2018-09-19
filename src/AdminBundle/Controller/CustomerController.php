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

use AdminBundle\Entity\Customer;
use AdminBundle\Form\CustomerType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/admin/customer")
 */
class CustomerController extends Controller
{
    /**
     * @Route("/", name="customer_index")
     */
    public function indexAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('AdminBundle:Customer');

        $customers = array();

        $form = $this->createFormBuilder(array())
            ->add('query', TextType::class, ['label' => false, 'attr' => ['placeholder' => 'Search by Id, KvK, e-mail or name']])
            ->add('submit', SubmitType::class, ['label' => 'Search'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        }
        else
        {
            $customers = $repo->findAll();
        }

        return $this->render('AdminBundle:Customer:index.html.twig', array(
            'customers' => $customers,
            'form' => $form->createView()
            ));
    }

    /**
     * @Route("/edit/{id}", name="customer_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if ($id == 0)
        {
            $customer = new Customer();
        }
        else
        {
            $customer = $em->getRepository('AdminBundle:Customer')->find($id);
        }

        $form = $this->createForm(CustomerType::class, $customer);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($customer);
            $em->flush();

            return $this->redirectToRoute('customer_index');
        }


        return $this->render('AdminBundle:Customer:edit.html.twig', array(
                'form' => $form->createView(),
            ));
    }

    /**
     * @Route("/delete/{id}", name="customer_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $customer = $em->getRepository('AdminBundle:Customer')->find($id);
        $em->remove($customer);
        $em->flush();

        return $this->redirectToRoute('customer_index');
    }
}
