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

use AppBundle\Entity\OrderStatus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/admin/orderstatus")
 */
class OrderStatusController extends Controller
{
    /**
     * @Route("/", name="orderstatus_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $orderStatuses = $em->getRepository("AppBundle:OrderStatus")->findBy(array(), array('pindex' => 'ASC'));

        return $this->render('AppBundle:OrderStatus:index.html.twig', array(
            'orderStatuses' => $orderStatuses));
    }

    /**
     * @Route("/edit/{id}", name="orderstatus_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if ($id == 0)
        {
            $status = new OrderStatus();
        }
        else
        {
            $status = $em->getRepository('AppBundle:OrderStatus')->find($id);
        }

        $form = $this->createFormBuilder($status)
                ->add('pindex', IntegerType::class, ['required' => false])
                ->add('name')
                ->add('isSale', CheckboxType::class, ['required' => false])
                ->add('isPurchase', CheckboxType::class, ['required' => false])
                ->add('save', SubmitType::class, array('attr' => ['class' => 'btn-success btn-120']))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($status);
            $em->flush();

            return $this->redirectToRoute('orderstatus_index');
        }

        return $this->render('AppBundle:OrderStatus:edit.html.twig', array(
                'form' => $form->createView(),
            ));
    }

    /**
     * @Route("/delete/{id}", name="orderstatus_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($em->getReference(OrderStatus::class, $id));
        $em->flush();

        return $this->redirectToRoute('orderstatus_index');
    }
}
