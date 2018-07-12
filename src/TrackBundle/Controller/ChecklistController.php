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

namespace TrackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use TrackBundle\Entity\Product;
use TrackBundle\Entity\ProductTask;

/**
 * @Route("track/task")
 */
class ChecklistController extends Controller
{
    
    /**
     * @Route("/show/{id}", name="track_checklist_show")
     * @Method({"GET"})
     */
    public function showListAction(Product $product) {
        $em = $this->getDoctrine()->getManager();
        
        $tasks = $em->getRepository("TrackBundle:ProductTask")->createQueryBuilder('t')
                ->where('t.productId = :q')
                ->setParameter('q', $product->getId())
                ->getQuery();
        
        $tasks = $tasks->getResult();
        
        return $this->render("TrackBundle:Track:Checklist/list.html.twig", [
            'product' => $product,
            'tasks' => $tasks,
        ]);
    }
}
