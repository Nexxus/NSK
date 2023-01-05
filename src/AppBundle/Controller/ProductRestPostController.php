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
use AppBundle\Entity\ProductAttributeFile;
use AppBundle\Entity\ProductAttributeRelation;
use AppBundle\Entity\ProductType;
use AppBundle\Entity\ProductStatus;
use AppBundle\Entity\Location;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;

/**
 * This controller will replace the ProductController gradually
 * because of implementation of Vue (issue #324)
 */
class ProductRestPostController extends FOSRestController
{
    /**
     * @Rest\Post("/edit", name="rest_post_edit")
     * @Rest\View(serializerGroups={"product:edit"})
     */
    public function editAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \AppBundle\Repository\ProductRepository */
        $repo = $em->getRepository('AppBundle:Product');

        $id = $request->get("id");

        if (!$id)
        {
            $product = new Product();

            $salesOrderId = $request->get("salesOrderId");
            $purchaseOrderId = $request->get("purchaseOrderId");

            if ($salesOrderId > 0 && $purchaseOrderId > 0) // new backorder
            {
                $repo->generateProductOrderRelation($product, $em->find(SalesOrder::class, $salesOrderId));
                $repo->generateProductOrderRelation($product, $em->find(PurchaseOrder::class, $purchaseOrderId), 0);
            }
            elseif ($salesOrderId > 0) // new repair order
            {
                $repo->generateProductOrderRelation($product, $em->find(SalesOrder::class, $salesOrderId));
            }
            elseif ($purchaseOrderId > 0) // new normal purchase
            {
                $repo->generateProductOrderRelation($product, $em->find(PurchaseOrder::class, $purchaseOrderId));
            }            
        }
        else
        {
            /** @var Product */
            $product = $repo->find($id);
        }

        $repo->generateProductAttributeRelations($product);

        $product->setSku($request->get("sku") ? $request->get("sku") : time());
        $product->setName($request->get("name"));
        $product->setDescription($request->get("description"));
        $product->setPrice($request->get("price"));
        $product->setType($request->get("type") ? $em->find(ProductType::class, $request->get("type")['id']) : null);
        $product->setLocation($request->get("location") ? $em->find(Location::class, $request->get("location")['id']) : null);
        $product->setStatus($request->get("status") ? $em->find(ProductStatus::class, $request->get("status")['id']) : null);





        // TODO


        return $product;
    }

    /**
     * @Rest\Post("/bulkedit")
     */
    public function bulkeditAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $location = $request->get('location') ? $em->find(Location::class, $request->get('location')) : null;
        $status = $request->get('status') ? $em->find(ProductStatus::class, $request->get('status')) : null;
        $products = $em->getRepository(Product::class)->findBy(['id' => $request->get('productIds')]);        

        foreach ($products as $product)
        {
             /** @var Product $product */

            if ($location)
                $product->setLocation($location);

            if ($status)
                $product->setStatus($status);
        }
        
        $em->flush();

        return new Response();
    }

    /**
     * @Rest\Post("/split")
     */
    public function splitAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $product = $em->find(Product::class, $request->get('id'));
        $productStatus = $em->find(ProductStatus::class, $request->get('status'));
        $quantity = 0;
        $individualize = true;
        $sales = false;

        switch ($request->get('how')) {
            case "split_stockpart":
                $individualize = false;
            case "individualize_stockpart":
                $quantity = $request->get('quantity');
                break;
            case "individualize_stock":
                $quantity = $product->getStock()->getStock() - 1;
                break;                    
            case "individualize_bundle":
                $quantity = $product->getStock()->getPurchased() - 1;
                $sales = true;
                break;
        }

        $em->getRepository(Product::class)->splitProduct($product, $productStatus, $quantity, $individualize, $sales, $request->get('newSku'));

        $em->flush();  
        
        return new Response();
    }

    /**
     * @Rest\Post("/checklist", name="rest_post_checklist")
     */
    public function checklistAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \AppBundle\Repository\ProductRepository */
        $repo = $em->getRepository(Product::class);

        /** @var Product $product */
        $product = $repo->find($request->get('productId'));
        $purchaseOrderRelation = $product->getPurchaseOrderRelation();

        $repo->generateTaskServices($purchaseOrderRelation);    

        $services = $request->get('services');

        foreach ($services as $serviceArray) {
            $service = $purchaseOrderRelation->getTaskService($serviceArray['task']['id']);
            $service->setStatus($serviceArray['status']);

            if (array_key_exists('description', $serviceArray))
                $service->setDescription($serviceArray['description']);
        }

        $em->flush();

        return new Response();
    }

    /**
     * @Rest\Post("/delete")
     */
    public function deleteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->find(Product::class, $request->get('id'));
        $em->remove($product);
        $em->flush();

        return new Response();
    }

    /**
     * @Rest\Post("/deletefile")
     */
    public function deleteFileAction(Request $request)
    {
        $fileId = $request->get('fileId');
        $attributeId = $request->get('attributeId');

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
