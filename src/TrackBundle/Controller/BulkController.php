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

use TrackBundle\Entity\Product;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for editing multiple products in one form
 * 
 * @Route("/track/bulk")
 */
class BulkController extends ProductController
{
    /**
     * @Route("/edit", name="track_bulk_edit")
     * @Method({"GET", "POST"})
     */
    public function bulkEditAction(Request $request)
    {
        $msg = "";
        
        $em = $this->getDoctrine()->getManager();
        
        $ids = $request->query->get('id');
        
        $products = $this->getProductsByIds($ids);
        
        // if all items are the same type, give attribute options
        if($this->ifProductTypeEqual($products)) {
            foreach($products as $product) {
                // set product for base values
                if(!isset($baseproduct)) {
                    $baseproduct = $product;
                }
                // add attributes
                $product->attributes = $this->getProductAttributes($product);
            }
        }

        $bulkForm = $this->createFormBuilder($baseproduct)
                ->add('sku', TextType::class)
                ->add('name', TextType::class)
                ->add('quantity', IntegerType::class, array(
                    'required' => false
                ))
                ->add('price', IntegerType::class, array(
                    'required' => false
                ))
                ->add('location',  EntityType::class, array(
                    'class' => 'TrackBundle:Location',
                    'choice_label' => 'name'
                ))
                ->add('type',  EntityType::class, array(
                    'class' => 'TrackBundle:ProductType',
                    'choice_label' => 'name'
                ))
                ->add('description', TextType::class, array(
                    'required' => false
                ))
                ->add('status')
                ->add('brand', TextType::class, array(
                    'required' => false
                ))
                ->add('department', TextType::class, array(
                    'required' => false
                ))
                ->add('owner', TextType::class, array(
                    'required' => false
                ));
        
        $bulkForm->add('save', SubmitType::class, ['label' => 'Save Changes']);
        
        $bulkForm = $bulkForm->getForm();
        
        $bulkForm = $bulkForm->handleRequest($request);
        
        // on submit,
        // check if all products have attributes
        $editForm = $this->addProductDataToForm($lastproduct);
        
        $editForm->add('save', SubmitType::class, ['label' => 'Save Changes']);
      
        $editForm = $editForm->getForm();
       
        $editForm->handleRequest($request);
        
        if($editForm->isSubmitted()) {
            $product = $editForm->getData();
            $msg = "Bulk edit has been processed.";
            
            $idstr = "";
            foreach($ids as $id) {
                $idstr .= $id . ","; 
            } 
            $idstr = rtrim($idstr, ",");
            print_r($idstr);
            
            $query = $em->createQuery("UPDATE "
                    . " TrackBundle:Product p"
                    . " SET"
                    . "  p.name = :name,"
                    . "  p.quantity = :quantity, "
                    . "  p.price = :price,"
                    . "  p.description = :description,"
                    . "  p.type = :type,"
                    . "  p.status = :status"
                    . " WHERE"
                    . "  p.id IN ({$idstr})")
                    ->setParameter("name", $product->getName())
                    ->setParameter("quantity", $product->getQuantity())
                    ->setParameter("price", $product->getPrice())
                    ->setParameter("description", $product->getDescription())
                    ->setParameter("type", $product->getType())
                    ->setParameter("status", $product->getStatus());
            
            $query = $query->getResult();
            
            return $this->redirectToRoute('track_index');
        }
        
        // create form for attributes
        return $this->render('TrackBundle:Bulk:edit.html.twig', array(
            'edit_form' => $editForm->createView(),
            'sellable'  => PRODUCT_SELLABLE,
        ));
    }

    /**
     * Loops through array of products 
     * returns true if products are the same type
     * 
     * @param type $products
     * @return boolean
     */
    public function ifProductTypeEqual($products) {
        $equal = true; 
        $lasttype = ""; 
        
        $i=0; 
        foreach($products as $product) {
            if($i>0 && ($lasttype != $product->getType())) {
                $equal = false;
            }
            
            $lasttype = $product->getType();
            $i++;
        }
        
        return $equal;
    }
    
}
