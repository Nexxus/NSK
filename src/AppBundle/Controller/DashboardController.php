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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\IndexSearchForm;

/**
 * Product controller.
 *
 * @Route("/")
 */
class DashboardController extends Controller
{
    /**
     * @Route("/", name="home")
     * @Method({"GET", "POST"})
     */
    public function indexAction(Request $request)
    {
        $result = null;

        $form = $this->createForm(IndexSearchForm::class, array(), array('withRadioButtons' => true));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            if ($data['type'] == 'barcode')
            {
                $repo = $this->getDoctrine()->getRepository('AppBundle:Product');
                $result = $repo->findByBarcodeSearchQuery($data['query']);
            }
            else
            {
                return $this->redirectToRoute($data['type'] . '_index', ['index_search_form[query]' => $data['query'] ]);
            }
        }

        return $this->render('AppBundle:Dashboard:index.html.twig', array(
            'result' => $result,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("underconstruction", name="underconstruction")
     */
    public function underConstructionAction()
    {
        return $this->render('AppBundle::underconstruction.html.twig');
    }
}


