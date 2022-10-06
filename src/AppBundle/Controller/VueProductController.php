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
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use AppBundle\Entity\ProductAttributeRelation;

/**
 * This controller will replace the ProductController gradually
 * because of implementation of Vue (issue #324)
 * It uses ajax/rest
 */
class VueProductController extends FOSRestController
{
    /**
     * @Rest\Get("/index")
     * @Rest\View(serializerGroups={"product:index"})
     */
    public function indexAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository(Product::class);

        $searchContainer = new \AppBundle\Helper\IndexSearchContainer($this->getUser(), Product::class);
        $searchContainer->query = $request->get('query');
        $searchContainer->availability = $request->get('availability');
        $searchContainer->status = $request->get('status') ? $this->getDoctrine()->getManager()->find('AppBundle:ProductStatus', $request->get('status')) : null;
        $searchContainer->type = $request->get('type') ? $this->getDoctrine()->getManager()->find('AppBundle:ProductType', $request->get('type')) : null;
        $searchContainer->location = $request->get('location') ? $this->getDoctrine()->getManager()->find('AppBundle:Location', $request->get('location')) : null;
        
        $offset = $request->get('offset');
        $limit = $request->get('limit');
        $sort = $request->get('sort');

        if ($searchContainer->isSearchable())
        {
            $products = $repo->querySearch($searchContainer, $offset, $limit, $sort)->getQuery()->getResult();
        }
        else
        {
            $products = $repo->queryStock($this->getUser(), $offset, $limit, $sort)->getQuery()->getResult();
        }

        return $products;     
    }

    /**
     * @Rest\Get("/meta")
     * @Rest\View(serializerGroups={"product:meta"})
     */
    public function metaAction(Request $request)
    {
        $productStatuses = $this->getDoctrine()->getRepository('AppBundle:ProductStatus')->findBy([], ['name' => 'ASC']);
        $productTypes = $this->getDoctrine()->getRepository('AppBundle:ProductType')->findBy([], ['name' => 'ASC']);
        $locations = $this->getDoctrine()->getRepository('AppBundle:Location')->findMine($this->getUser());

        return array(
            'productStatuses' => $productStatuses,
            'productTypes' => $productTypes,
            'locations' => $locations
        );     
    }  
    
    /**
     * @Rest\Get("/edit/{productId}")
     * @Rest\View(serializerGroups={"product:edit"})
     */
    public function editAction(Request $request, $productId)
    {
        /** @var \AppBundle\Repository\ProductRepository */
        $repo = $this->getDoctrine()->getRepository('AppBundle:Product');

        if ($productId == 0)
        {
            $product = new Product();
        }
        else
        {
            /** @var Product */
            $product = $repo->find($productId);
        }
        
        $repo->generateProductAttributeRelations($product);

        return $product; 
    }   

    /**
     * @Rest\Get("/checklist/{productId}")
     * @Rest\View(serializerGroups={"product:checklist"})
     */
    public function checklistAction(Request $request, $productId)
    {
        $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($productId);

        return $product->getPurchaseOrderRelation();  
    }     

    /**
     * @Rest\Get("/attributable/{productId}/{attributeId}")
     * @Rest\View(serializerGroups={"product:attributable"})
     */
    public function attributableProductsAction(Request $request, $productId, $attributeId)
    {
        $product = $this->getDoctrine()->getManager()->find('AppBundle:Product', $productId);
        $attribute = $this->getDoctrine()->getManager()->find('AppBundle:Attribute', $attributeId);
        $products = $this->getDoctrine()->getRepository('AppBundle:Product')->findAttributableProducts($product, $attribute);

        return $products; 
    }      
}
