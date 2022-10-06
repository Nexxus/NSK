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
 * Copiatek - info@copiatek.nl - Postbus 547 2501 CM Den Haag
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serialize;

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
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serialize\Groups({"product:edit", "product:checklist"})
     */
    private $id;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="orderRelations")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     */
    private $product;

    /**
     * @var AOrder|PurchaseOrder|SalesOrder
     *
     * @ORM\ManyToOne(targetEntity="AOrder", inversedBy="productRelations", fetch="EAGER")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false)
     * @Serialize\Groups({"product:edit"})
     */
    private $order;

    /**
     * @var int Actual quantity of this product in this order
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Serialize\Groups({"product:edit"})
     */
    private $quantity;

    /**
     * @var int Actual price of this product in this order, in eurocents, per unit
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $price;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $externalId;       

    /**
     * Services that are applied to this Product
     * SalesServices in case of SalesOrder
     * TaskServices in case of PurchaseOrder
     *
     * @var ArrayCollection|AService[]
     * @ORM\OneToMany(targetEntity="AService", mappedBy="productOrderRelation", cascade={"all"}, orphanRemoval=true)
     */
    private $services;

    public function getId()
    {
        return $this->id;
    }

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
     * Set external id like from webshop
     *
     * @param integer $name
     *
     * @return ProductOrderRelation
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * Set external id like from webshop
     *
     * @return int
     */
    public function getExternalId()
    {
        return $this->externalId;
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
     * @param AService $service
     *
     * @return ProductOrderRelation
     */
    public function addService(AService $service)
    {
        $this->services[] = $service;

        return $this;
    }

    public function removeService(AService $service)
    {
        $this->services->removeElement($service);
    }

    /**
    * @Serialize\VirtualProperty()
    * @Serialize\Groups({"product:checklist"})
    */
    public function getServices()
    {
        return $this->services;
    }

    /**
    * @Serialize\VirtualProperty()
    * @Serialize\Groups({"product:checklist"})
    */
    public function getServicesDone() {
        $servicesDone = $this->services->filter(function (AService $service) {
            return $service->getStatus() == AService::STATUS_DONE;
        });

        return $servicesDone->count() ?? 0;
    }
}
