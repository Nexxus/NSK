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

namespace AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use TrackBundle\Entity\Product;
use TrackBundle\Entity\PurchaseOrder;

/**
 * Partner
 *
 * @ORM\Entity(repositoryClass="AdminBundle\Repository\PartnerRepository")
 */
class Partner extends ACompany
{
    public function __construct() {
        $this->products = new ArrayCollection();
        $this->orders = new ArrayCollection();
        parent::__construct();
    }

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isPartner;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isOwner;

    /**
     * @var ArrayCollection|Product[] Products that this partner owns
     *
     * @ORM\OneToMany(targetEntity="TrackBundle\Entity\Product", mappedBy="owner", fetch="LAZY")
     */
    private $products;

    /**
     * @var ArrayCollection|PurchaseOrder[] Purchase orders that this partner supplied
     *
     * @ORM\OneToMany(targetEntity="TrackBundle\Entity\PurchaseOrder", mappedBy="supplier", fetch="LAZY")
     */
    private $orders;

    /**
     * @param bool $name
     *
     * @return Partner
     */
    public function setIsPartner($isPartner)
    {
        $this->isPartner = $isPartner;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsPartner()
    {
        return $this->isPartner;
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     * @return Partner
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
     * @return Partner
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
