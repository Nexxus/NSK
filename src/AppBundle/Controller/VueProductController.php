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

/**
 * This controller will replace the ProductController gradually
 * because of implementation of Vue (issue #324)
 * It uses ajax/rest
 */
class VueProductController extends FOSRestController
{
    /**
     * @Rest\Get("/{offset}/{limit}/{sort}/{order}")
     * @Rest\View(serializerGroups={"vue:products"})
     */
    public function indexAction(Request $request, $offset, $limit, $sort, $order)
    {
        $repo = $this->getDoctrine()->getRepository(Product::class);

        $products = $repo->queryStock($this->getUser(), $offset, $limit, $sort, $order)->getQuery()->getResult();

        return $products;     
    }
}
