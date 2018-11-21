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

use AppBundle\Entity\ProductStatus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/admin/productstatus")
 */
class ProductStatusController extends Controller
{
    /**
     * @Route("/", name="productstatus_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $productStatuses = $em->getRepository("AppBundle:ProductStatus")->findBy(array(), array('pindex' => 'ASC'));

        return $this->render('AppBundle:ProductStatus:index.html.twig', array(
            'productStatuses' => $productStatuses));
    }

    /**
     * @Route("/edit/{id}", name="productstatus_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if ($id == 0)
        {
            $status = new ProductStatus();
        }
        else
        {
            $status = $em->getRepository('AppBundle:ProductStatus')->find($id);
        }

        $form = $this->createFormBuilder($status)
                ->add('pindex', IntegerType::class)
                ->add('name')
                ->add('isStock', CheckboxType::class, ['required' => false])
                ->add('isStockSaleable', CheckboxType::class, ['required' => false])
               ->add('save', SubmitType::class, array('attr' => ['class' => 'btn-success btn-120']))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($status);
            $em->flush();

            return $this->redirectToRoute('productstatus_index');
        }


        return $this->render('AppBundle:ProductStatus:edit.html.twig', array(
                'form' => $form->createView(),
            ));
    }

    /**
     * @Route("/delete/{id}", name="productstatus_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($em->getReference(ProductStatus::class, $id));
        $em->flush();

        return $this->redirectToRoute('productstatus_index');
    }
}
