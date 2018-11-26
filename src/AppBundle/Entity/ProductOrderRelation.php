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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ProductOrderRelation
 *
 * @ORM\Table(name="product_order")
 * @ORM\Entity
 */
class ProductOrderRelation
{
    public function __construct(Product $product, AOrder $order) {
        $this->product = $product;
        $this->order = $order;
        $product->addOrderRelation($this);
        $order->addProductRelation($this);
        $this->services = new ArrayCollection();
    }

    /**
     * @var Product
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="orderRelations", fetch="EAGER")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     */
    private $product;

    /**
     * @var AOrder|PurchaseOrder|SalesOrder
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="AOrder", inversedBy="productRelations", fetch="EAGER")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false)
     */
    private $order;

    /**
     * @var int Actual quantity of this product in this order
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantity;

    /**
     * @var int Actual price of this product in this order, in eurocents, per unit
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $price;

    /**
     * @var ArrayCollection|Service[] Services that are applied to this Product
     * @ORM\OneToMany(targetEntity="Service", mappedBy="product", fetch="LAZY", cascade={"all"}, orphanRemoval=true)
     */
    private $services;

    /**
     * Get product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Get order
     *
     * @return AOrder|PurchaseOrder|SalesOrder
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set actual quantity of this product in this order
     *
     * @param integer $quantity
     *
     * @return ProductOrderRelation
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get actual quantity of this product in this order
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity !== null ? $this->quantity : 1;
    }

    /**
     * Set actual price of this product in this order, in euros (float), per unit
     *
     * @param float $price
     *
     * @return ProductOrderRelation
     */
    public function setPrice($price)
    {
        $this->price = $price * 100;

        return $this;
    }

    /**
     * Get actual price of this product in this order, in euros (float), per unit
     *
     * @return float
     */
    public function getPrice()
    {
        return floatval($this->price) / 100;
    }

    /**
     * Add service
     *
     * @param Service $service
     *
     * @return ProductOrderRelation
     */
    public function addService(Service $service)
    {
        $this->services[] = $service;

        return $this;
    }

    /**
     * Remove service
     *
     * @param Service $service
     */
    public function removeService(Service $service)
    {
        $this->services->removeElement($service);
    }

    /**
     * Get services
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getServices()
    {
        return $this->services;
    }
}
