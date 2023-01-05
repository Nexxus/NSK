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

use AppBundle\Entity\Product;
use AppBundle\Entity\ProductOrderRelation;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/product")
 */
class ProductController extends Controller
{
    use PdfControllerTrait;

    /**
     * @Route("/", name="product_index")
     */
    public function indexAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Product');

        $productCount = $repo->queryStock($this->getUser())->select("count(p.id)")->getQuery()->getSingleScalarResult();
        $pageLength = 20;

        return $this->render('AppBundle:Product:index.html.twig', array(
            'productCount' => $productCount,
            'pageLength' => $pageLength
            ));
    }

    /**
     * Temporarily solution as long as New Product section is Vue and Order sections are Twig.
     * The route is rather complex:
     * 
     * 1. New product selection by user in PurchaseOrder/edit.html.twig (and same for SalesOrder)
     * 2. This action ProductController::new (just forwarding props)
     * 3. Bare Twig container file Product/new.html.twig (again forwarding props)
     * 4. Entrypoint config in webpack.config.js
     * 5. ProductNew/app.js
     * 6. ProductNew/App.vue (and again forwarding props)
     * 7. Reuse of ModalEdit.vue (originally made for product edit)
     * 8. Retrieval of product object by AJAX in VueProductController::new 
     * 
     * @Route("/new/{purchaseOrderId}/{salesOrderId}/{productTypeId}", name="product_new")
     */
    public function newAction(Request $request, $purchaseOrderId = 0, $salesOrderId = 0, $productTypeId = 0)
    {
        return $this->render('AppBundle:Product:new.html.twig', array(
            'purchaseOrderId' => $purchaseOrderId,
            'salesOrderId' => $salesOrderId,
            'productTypeId' => $productTypeId
            ));
    }    

    /**
     * @Route("/print/{id}", name="product_print")
     */
    public function printAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('AppBundle:Product')->find($id);

        $html = $this->render('AppBundle:Product:print.html.twig', array('product' => $product));
        $mPdfConfiguration = ['', 'A6' ,'','',0,0,0,0,0,0,'P'];

        return $this->getPdfResponse("Nexxus price tag", $html, $mPdfConfiguration);
    }

    /**
     * @Route("/checklistprint/{relationId}", name="product_checklist_print")
     */
    public function printChecklistAction(Request $request, $relationId)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var ProductOrderRelation */
        $relation = $em->find(ProductOrderRelation::class, $relationId);

        $em->getRepository('AppBundle:Product')->generateTaskServices($relation);

        $html = $this->render('AppBundle:Product:printchecklist.html.twig', array('relation' => $relation));
        $mPdfConfiguration = ['', 'A4' ,'','',10,10,10,10,0,0,'P'];

        //return new Response($html);

        return $this->getPdfResponse("Checklist", $html, $mPdfConfiguration);
    }


    /**
     * @Route("/bulkprint/{object}/{productIds}")
     */
    public function bulkprintAction(Request $request, $object, $productIds)
    {
        $em = $this->getDoctrine()->getManager();
        $html = array();
        $mPdfConfiguration = ['', 'A6' ,'','',0,0,0,0,0,0,'P'];
        $products = $em->getRepository(Product::class)->findBy(['id' => explode(",", $productIds)]);        

        if ($object == "checklists") {
            $products = array_filter($products, function (Product $product) {
                return $product->getPurchaseOrderRelation() && $product->getType() && $product->getType()->getTasks()->count();
            });
        }

        if (!count($products)) {
            $html = "No (valid) products to print";
        }
        else
        {
            foreach ($products as $product)
            {
                /** @var Product $product */

                $em->getRepository('AppBundle:Product')->generateTaskServices($product->getPurchaseOrderRelation());

                switch ($object) {
                    case "barcodes":
                        $html[] = $this->render('AppBundle:Barcode:single.html.twig', array('barcode' => $product->getSku()));
                        $mPdfConfiguration = ['', [54,25] ,'9','',3,'3',1,'','0','0','P'];
                        break;
                    case "pricecards":
                        $html[] = $this->render('AppBundle:Product:print.html.twig', array('product' => $product));
                        $mPdfConfiguration = ['', 'A6' ,'','',0,0,0,0,0,0,'P'];
                        break;
                    case "checklists":
                        $html[] = $this->render('AppBundle:Product:printchecklist.html.twig', array('relation' => $product->getPurchaseOrderRelation()));
                        $mPdfConfiguration = ['', 'A4' ,'','',10,10,10,10,0,0,'P'];
                        break;                 
                }
            }
        }

        //return new Response($html[0]);

        return $this->getPdfResponse("Bulk print", $html, $mPdfConfiguration);
    }    
}
