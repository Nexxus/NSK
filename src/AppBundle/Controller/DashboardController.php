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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
        $data = array('type' => 'purchaseOrder');

        $form = $this->createFormBuilder($data)
            ->add('query', TextType::class, ['label' => false])
            ->add('type', ChoiceType::class, [
                'label' => false,
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Producten in voorraad' => 'track',
                    'Inkooporders' => 'purchaseorder',
                    'Verkooporders' => 'salesorder',
                    'Klanten' => 'customer',
                    'Leveranciers' => 'supplier'
                    //'Locaties' => 'location'
                ]
            ])
            ->add('submit', SubmitType::class, ['label' => 'Search'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            return $this->redirectToRoute($data['type'] . '_index', ['query' => $data['query'] ]);
        }

        return $this->render('AppBundle:Dashboard:index.html.twig', array(
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


