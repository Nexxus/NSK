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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Controller for exporting CSVs
 */
class ExportController extends Controller
{
    
    /**
     * Exports a CSV file based on list of items, exports all columns
     * 
     * @Route("/track/export/csv", name="track_export_csv")
     * @Method("GET")
     */
    public function exportCSVAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        
        $selection = $request->query->get('id');
        
        // get items
        $query = $em->getRepository("AppBundle:Product")->createQueryBuilder('p');
        
        foreach($selection as $id) {
            $query->orWhere("p.id = {$id}");
        }
        
        $products = $query->getQuery()->getResult();
        
        // serialize into text
        $serialize = "SKU"
                . "\t" . "Name"
                . "\t" . "Quantity"
                . "\t" . "Location"
                . "\t" . "Type"
                . "\t" . "Status"
                . "\t" . "Brand"
                . "\t" . "Department"
                . "\t" . "Owner";
        
        foreach($products as $product) {
            $serialize .= "\n" . $product->getSku()
                        . "\t" . $product->getName()
                        . "\t" . $product->getQuantity()
                        . "\t" . $product->getLocation()
                        . "\t" . $product->getType()
                        . "\t" . $product->getStatus()
                        . "\t" . $product->getBrand()
                        . "\t" . $product->getDepartment() 
                        . "\t" . $product->getOwner();
        }
        
        $response = new Response(
                $serialize,
                Response::HTTP_OK,
                [
                    'Content-Disposition' => 'attachment; Filename=ProductExport.csv',
                    'Content-Type' => 'text/csv'
                ]);
        
        return $response;
    }
}
