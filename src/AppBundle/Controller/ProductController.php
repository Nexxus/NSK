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
use AppBundle\Entity\PurchaseOrder;
use AppBundle\Entity\SalesOrder;
use AppBundle\Entity\ProductAttributeFile;
use AppBundle\Entity\ProductAttributeRelation;
use AppBundle\Form\ProductForm;
use AppBundle\Form\ProductSplitForm;
use AppBundle\Form\IndexSearchForm;
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

        $container = new \AppBundle\Helper\IndexSearchContainer();
        $container->user = $this->getUser();
        $container->className = Product::class;

        $form = $this->createForm(IndexSearchForm::class, $container);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $container->isSearchable())
        {
            $products = $repo->findBySearchQuery($container);
        }
        else
        {
            $products = $repo->findStock($this->getUser());
        }

        $paginator = $this->get('knp_paginator');
        $productsPage = $paginator->paginate($products, $request->query->getInt('page', 1), 10);

        return $this->render('AppBundle:Product:index.html.twig', array(
            'products' => $productsPage,
            'form' => $form->createView()
            ));
    }

    /**
     * @Route("/new/{purchaseOrderId}/{backingSalesOrderId}/{productTypeId}", name="product_new")
     * @Route("/edit/{id}/{success}", name="product_edit")
     * @Route("/editsub/{refId}/{id}", name="product_subedit")
     */
    public function editAction(Request $request, $id = 0, $purchaseOrderId = 0, $backingSalesOrderId = 0, $productTypeId = 0, $success = null, $refId = null)
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


        if ($backingSalesOrderId > 0 && $purchaseOrderId > 0)
        {
            $repo->generateProductOrderRelation($product, $em->find(SalesOrder::class, $backingSalesOrderId));
            $repo->generateProductOrderRelation($product, $em->find(PurchaseOrder::class, $purchaseOrderId), 0);
        }
        elseif ($purchaseOrderId > 0)
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
                        $file = new ProductAttributeFile();
                        $file->setUniqueServerFilename($k);
                        $file->setOriginalClientFilename($v);
                        $file->setProduct($product);
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
     * @Route("/split/{id}/{success}", name="product_split")
     */
    public function splitAction(Request $request, $id, $success = null)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \AppBundle\Repository\ProductRepository */
        $repo = $em->getRepository('AppBundle:Product');

        /** @var Product */
        $product = $repo->find($id);

        $data = array('quantity' => 1, 'status' => $product->getStatus(), 'individualize' => false);
        $options = array('max' => $product->getQuantityInStock() - 1);

        $form = $this->createForm(ProductSplitForm::class, $data, $options);

        $form->handleRequest($request);

        if ($form->isSubmitted())
        {
            if ($form->isValid())
            {
                $data = $form->getData();
                $quantity = $data['quantity'];

                if ($data['individualize'] && $quantity > 1)
                {
                    for ($i = 1; $i <= $quantity; $i++)
                    {
                        $repo->splitProduct($product, $data['status'], 1, "(split ".$i.")");
                    }
                }
                else
                {
                    $repo->splitProduct($product, $data['status'], $quantity, "(split)");
                }

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
