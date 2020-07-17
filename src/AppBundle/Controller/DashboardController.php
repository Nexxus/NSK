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
 * Copiatek - info@copiatek.nl - Postbus 547 2501 CM Den Haag
 */

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\IndexSearchForm;
use AppBundle\Entity\PurchaseOrder;
use AppBundle\Entity\SalesOrder;
use AppBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;

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
        $em = $this->getDoctrine()->getManager();
        
        $result = null;

        $container = new \AppBundle\Helper\IndexSearchContainer($this->getUser(), null);
        
        $form = $this->createForm(IndexSearchForm::class, $container);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $container->isSearchable()) {

            if ($container->type == 'barcode')
            {
                $repo = $this->getDoctrine()->getRepository('AppBundle:Product');
                $result = $repo->findByBarcodeSearchQuery($container);
            }
            else
            {
                return $this->redirectToRoute($container->type . '_index', ['index_search_form[query]' => $container->query ]);
            }
        }

        return $this->render('AppBundle:Dashboard:index.html.twig', array(
            'pickups' => $em->getRepository(PurchaseOrder::class)->findLastPurchases($this->getUser(), true),
            'purchasesPerDay' => $em->getRepository(PurchaseOrder::class)->findPurchasesPerDay($this->getUser()),
            'sales' => $em->getRepository(SalesOrder::class)->findLastSales($this->getUser()),
            'salesPerDay' => $em->getRepository(SalesOrder::class)->findSalesPerDay($this->getUser()),
            'repairs' => $em->getRepository(SalesOrder::class)->findLastSales($this->getUser(), true),
            'repairsPerDay' => $em->getRepository(SalesOrder::class)->findRepairsPerDay($this->getUser()),
            'products' => $em->getRepository(Product::class)->findLastUpdated($this->getUser()),
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

    /**
     * @Route("/prestashopcommand", name="prestashopcommand")
     * @Method({"GET"})
     */
    public function prestashopcommandAction(Request $request)
    { 
        $kernel = $this->container->get('kernel');       
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'nexxus:prestashop',
            'productStatusIdFilter' => 1,
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);

        $content = $output->fetch();
        return new Response($content);
    }
}


