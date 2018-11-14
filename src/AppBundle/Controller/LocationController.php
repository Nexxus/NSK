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

use AppBundle\Entity\Location;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Location controller.
 *
 * @Route("admin/location")
 */
class LocationController extends Controller
{
    /**
     * Lists all location entities.
     *
     * @Route("/", name="admin_location_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $locations = $em->getRepository('AppBundle:Location')->findAll();

        return $this->render('AppBundle:Location:index.html.twig', array(
            'locations' => $locations,
        ));
    }

    /**
     * Displays a form to edit an existing location entity.
     *
     * @Route("/edit/{id}", name="admin_location_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if ($id == 0)
        {
            $location = new Location();
        }
        else
        {
            $location = $em->getRepository('AppBundle:Location')->find($id);
        }

        $form = $this->createFormBuilder($location)
                ->add('name')
                ->add('save', SubmitType::class, array('attr' => ['class' => 'btn-success btn-120']))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($location);
            $em->flush();

            return $this->redirectToRoute('admin_location_index');
        }

        return $this->render('AppBundle:Location:edit.html.twig', array(
            'location' => $location,
            'form' => $form->createView()
        ));
    }

    /**
     * Deletes a location entity.
     *
     * @Route("/delete/{id}", name="admin_location_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($em->getReference(Location::class, $id));
        $em->flush();

        return $this->redirectToRoute('admin_location_index');
    }
}
