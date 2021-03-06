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

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Product;
use AppBundle\Entity\PurchaseOrder;

/**
 * Supplier
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SupplierRepository")
 */
class Supplier extends ACompany
{
    public function __construct() {
        $this->products = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    /**
     * @var ArrayCollection|Product[] Products that this supplier owns
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Product", mappedBy="owner")
     * @JMS\Exclude()
     */
    private $products;

    /**
     * @var ArrayCollection|PurchaseOrder[] Purchase orders that this supplier supplied
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\PurchaseOrder", mappedBy="supplier", cascade={"persist"})
     * @JMS\Exclude()
     */
    private $orders;

    /**
     * @param PurchaseOrder $purchaseOrder
     * @return Supplier
     */
    public function addPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $this->orders[] = $purchaseOrder;

        return $this;
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     */
    public function removePurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $this->orders->removeElement($purchaseOrder);
    }

    /**
     * @return ArrayCollection|PurchaseOrder[]
     */
    public function getPurchaseOrders()
    {
        return $this->orders;
    }

    /**
     * @param Product $product
     * @return Supplier
     */
    public function addProduct(Product $product)
    {
        $this->products[] = $product;

        return $this;
    }

    /**
     * @param Product $product
     */
    public function removeProduct(Product $product)
    {
        $this->products->removeElement($product);
    }

    /**
     * @return ArrayCollection|Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }
}
