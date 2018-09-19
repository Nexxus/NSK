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

use AdminBundle\Entity\Partner;
use AdminBundle\Form\PartnerType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


/**
 * @Route("/admin/partner")
 */
class PartnerController extends Controller
{
    /**
     * @Route("/", name="partner_index")
     */
    public function indexAction()
    {
        $repo = $this->getDoctrine()->getRepository('AdminBundle:Partner');
        $partners = $repo->findAll();

        return $this->render('AdminBundle:Partner:index.html.twig', array(
            'partners' => $partners));
    }

    /**
     * @Route("/edit/{id}", name="partner_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if ($id == 0)
        {
            $partner = new Partner();
        }
        else
        {
            $partner = $em->getRepository('AdminBundle:Partner')->find($id);
        }

        $form = $this->createForm(PartnerType::class, $partner);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($partner);
            $em->flush();

            return $this->redirectToRoute('partner_index');
        }


        return $this->render('AdminBundle:Partner:edit.html.twig', array(
                'form' => $form->createView(),
            ));
    }

    /**
     * @Route("/delete/{id}", name="partner_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $partner = $em->getRepository('AdminBundle:Partner')->find($id);
        $em->remove($partner);
        $em->flush();

        return $this->redirectToRoute('partner_index');
    }
}
