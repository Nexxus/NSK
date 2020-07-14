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
use AppBundle\Entity\ProductType;
use AppBundle\Entity\ProductOrderRelation;
use AppBundle\Entity\PurchaseOrder;
use AppBundle\Entity\SalesOrder;
use AppBundle\Entity\ProductAttributeFile;
use AppBundle\Entity\ProductAttributeRelation;
use AppBundle\Form\ProductForm;
use AppBundle\Form\ProductSplitForm;
use AppBundle\Form\ChecklistForm;
use AppBundle\Form\IndexSearchForm;
use AppBundle\Form\IndexBulkEditForm;
use AppBundle\Form\ProductBulkEditForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
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

        $products = array();

        $container = new \AppBundle\Helper\IndexSearchContainer($this->getUser(), Product::class);

        $form = $this->createForm(IndexSearchForm::class, $container);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $container->isSearchable())
        {
            $products = $repo->findBySearchQuery($container);
            $pageLength = 200;
        }
        else
        {
            $products = $repo->findStock($this->getUser());
            $pageLength = 20;
        }

        $paginator = $this->get('knp_paginator');
        $productsPage = $paginator->paginate($products, $request->query->getInt('page', 1), $pageLength);

        return $this->render('AppBundle:Product:index.html.twig', array(
            'products' => $productsPage,
            'form' => $form->createView(),
            'formBulkEdit' => $this->createForm(IndexBulkEditForm::class, $products, ['index_class' => Product::class])->createView()
            ));
    }

    /**
     * @Route("/bulkedit/{success}", name="product_bulkedit")
     */
    public function bulkEditAction(Request $request, $success = null)
    {
        $em = $this->getDoctrine()->getManager();

        // Get variables from IndexBulkEditForm
        $action = $request->query->get('index_bulk_edit_form')['action'];
        $productIds = $request->query->get('index_bulk_edit_form')['index'];
        $products = $em->getRepository(Product::class)->findBy(['id' => $productIds]);

        if ($action == "status")
        {
            $form = $this->createForm(ProductBulkEditForm::class, $products, array('user' => $this->getUser()));

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {
                $location = $form->get("location")->getData();
                $status = $form->get("status")->getData();
                
                foreach ($products as $product)
                {
                     /** @var Product $product */

                    if ($location)
                        $product->setLocation($location);

                    if ($status)
                        $product->setStatus($status);
                }
                
                $em->flush();

                return $this->redirectToRoute("product_bulkedit", array('index_bulk_edit_form[action]' => $action, 'index_bulk_edit_form[index]' => $productIds, 'success' => true));
            }
            else if ($form->isSubmitted())
            {
                $success = false;
            }

            return $this->render('AppBundle:Product:bulkedit.html.twig', array(
                'form' => $form->createView(),
                'success' => $success
            ));
        }
        else {
            return $this->bulkPrint($action, $products);
        }
    }

    /**
     * @param string $object
     * @param Product[] $products
     */
    private function bulkPrint($object, $products) {

        $em = $this->getDoctrine()->getManager();
        $html = array();
        $mPdfConfiguration = ['', 'A6' ,'','',0,0,0,0,0,0,'P'];;

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

    /**
     * @Route("/new/{purchaseOrderId}/{salesOrderId}/{productTypeId}", name="product_new")
     * @Route("/edit/{id}/{success}", name="product_edit")
     * @Route("/editsub/{refId}/{id}", name="product_subedit")
     */
    public function editAction(Request $request, $id = 0, $purchaseOrderId = 0, $salesOrderId = 0, $productTypeId = 0, $success = null, $refId = null)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \AppBundle\Repository\ProductRepository */
        $repo = $em->getRepository('AppBundle:Product');

        if ($id == 0)
        {
            $product = new Product();
        }
        else
        {
            /** @var Product */
            $product = $repo->find($id);
        }

        if ($productTypeId > 0)
        {
            $product->setType($em->getReference(ProductType::class, $productTypeId));
        }

        if ($salesOrderId > 0 && $purchaseOrderId > 0) // backorder
        {
            $repo->generateProductOrderRelation($product, $em->find(SalesOrder::class, $salesOrderId));
            $repo->generateProductOrderRelation($product, $em->find(PurchaseOrder::class, $purchaseOrderId), 0);
        }
        elseif ($salesOrderId > 0) // repair order
        {
            $repo->generateProductOrderRelation($product, $em->find(SalesOrder::class, $salesOrderId));
        }
        elseif ($purchaseOrderId > 0) // normal purchase
        {
            $repo->generateProductOrderRelation($product, $em->find(PurchaseOrder::class, $purchaseOrderId));
        }

        $repo->generateProductAttributeRelations($product);

        $form = $this->createForm(ProductForm::class, $product, array('user' => $this->getUser()));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            // Generate Sku if necessary
            if (!$product->getSku())
            {
                $product->setSku(time());
            }

            // Files
            foreach ($form->get('attributeRelations') as $attributeRelationForm)
            {
                if ($attributeRelationForm->has('valueFiles'))
                {
                    /** @var ProductAttributeRelation */
                    $r = $attributeRelationForm->getData();

                    $fileNames = UploadifiveController::splitFilenames($attributeRelationForm->get('value')->getData());

                    foreach ($fileNames as $k => $v)
                    {
                        $file = new ProductAttributeFile($product, $v, $k);
                        $em->persist($file);
                        $em->flush($file);

                        $val = $r->getValue() ? $r->getValue() . "," . $file->getId() : $file->getId();
                        $r->setValue($val);
                    }
                }
            }

            $em->persist($product);

            try {
                $em->flush();
            }
            catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                $form->get('sku')->addError(new FormError('Already exists'));
                $success = false;
            }

            if ($success !== false)
                return $this->redirectToRoute("product_edit", array('id' => $product->getId(), 'success' => true));
        }
        else if ($form->isSubmitted())
        {
            $success = false;
        }

        return $this->render('AppBundle:Product:edit.ajax.twig', array(
                'product' => $product,
                'form' => $form->createView(),
                'formAction' => $request->getRequestUri(),
                'success' => $success,
                'refId' => $refId,
            ));
    }

    /**
     * @Route("/split/{id}", name="product_split")
     */
    public function splitAction(Request $request, $id)
    {
        $success = null;

        $em = $this->getDoctrine()->getManager();

        /** @var \AppBundle\Repository\ProductRepository */
        $repo = $em->getRepository('AppBundle:Product');

        /** @var Product */
        $product = $repo->find($id);

        $data = array('quantity' => 1, 'status' => $product->getStatus());
        $options = array('stock' => $product->getQuantityInStock());

        $form = $this->createForm(ProductSplitForm::class, $data, $options);

        $form->handleRequest($request);

        if ($form->isSubmitted())
        {
            if ($form->isValid())
            {
                $data = $form->getData();
                $quantity = 0;
                $individualize = true;
                $sales = false;

                switch ($data['how']) {
                    case "split_stockpart":
                        $individualize = false;
                    case "individualize_stockpart":
                        $quantity = $data['quantity'];
                        break;
                    case "individualize_stock":
                        $quantity = $product->getQuantityInStock() - 1;
                        break;                    
                    case "individualize_bundle":
                        $quantity = $product->getQuantityPurchased() - 1;
                        $sales = true;
                        break;
                }

                $repo->splitProduct($product, $data['status'], $quantity, $individualize, $sales, $data['newSku']);

                try {
                    $em->flush();
                    $success = true;
                }
                catch (\Exception $e) {
                    $form->get('quantity')->addError(new FormError($e->getMessage()));
                    $success = false;
                }
            }
            else
            {
                $success = false;
            }
        }

        return $this->render('AppBundle:Product:split.ajax.twig', array(
                'product' => $product,
                'form' => $form->createView(),
                'formAction' => $request->getRequestUri(),
                'success' => $success
            ));
    }

    /**
     * @Route("/checklist/{relationId}", name="product_checklist")
     */
    public function checklistAction(Request $request, $relationId)
    {
        $success = null;

        $em = $this->getDoctrine()->getManager();

        /** @var ProductOrderRelation */
        $relation = $em->find(ProductOrderRelation::class, $relationId);

        /** @var \AppBundle\Repository\ProductRepository */
        $repo = $em->getRepository('AppBundle:Product');

        $repo->generateTaskServices($relation);

        $form = $this->createForm(ChecklistForm::class, $relation);

        $form->handleRequest($request);

        if ($form->isSubmitted())
        {
            if ($form->isValid())
            {
                $em->persist($relation);

                try {
                    $em->flush();
                    $success = true;
                }
                catch (\Exception $e) {
                    $success = false;
                }
            }
            else
            {
                $success = false;
            }
        }

        return $this->render('AppBundle:Product:checklist.ajax.twig', array(
                'relation' => $relation,
                'form' => $form->createView(),
                'formAction' => $request->getRequestUri(),
                'success' => $success
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
     * @Route("/delete/{id}", name="product_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($em->getReference(Product::class, $id));
        $em->flush();

        return $this->redirectToRoute('product_index');
    }

    /**
     * @Route("/deletefile", name="product_file_delete")
     */
    public function deleteFileAction(Request $request)
    {
        $fileId = $request->request->get('fileId');
        $attributeId = $request->request->get('attributeId');

        $em = $this->getDoctrine()->getManager();

        /** @var ProductAttributeFile */
        $file = $em->find(ProductAttributeFile::class, $fileId);

        /** @var ProductAttributeRelation */
        $relation = $em->find(ProductAttributeRelation::class, array("product" => $file->getProduct(), "attribute" => $attributeId));
        $val = $relation->getValue();
        $val = str_replace(" ", "", $val);
        $vals = explode(",", $val);

        // remove file id from relation value
        if (($key = array_search($fileId, $vals)) !== false) {
            unset($vals[$key]);
        }

        $relation->setValue(implode(",", $vals));

        $em->persist($relation);
        // $em->remove($file); can be used by other products after split
        $em->flush();

        return new Response();
    }
}
