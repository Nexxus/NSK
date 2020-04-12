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

/**
 * AOrder
 *
 * @ORM\Table(name="aorder")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"s" = "SalesOrder", "p" = "PurchaseOrder"})
 */
abstract class AOrder
{
    public function __construct() {
        $this->productRelations = new ArrayCollection();
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=16, unique=true, nullable=true)
     */
    protected $orderNr;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $remarks;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $orderDate;

    /**
     * @var ArrayCollection|ProductOrderRelation[] Which products have this attribute
     *
     * @ORM\OneToMany(targetEntity="ProductOrderRelation", mappedBy="order", cascade={"all"}, orphanRemoval=true)
     */
    protected $productRelations;

    /**
     * @var OrderStatus
     *
     * @ORM\ManyToOne(targetEntity="OrderStatus", fetch="EAGER")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    protected $status;

    /**
     * @var int Discount price, in eurocents
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $discount;

    /**
     * @var int Price for transport, in eurocents
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $transport;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $isGift;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param bool $isGift
     */
    public function setIsGift($isGift)
    {
        $this->isGift = $isGift;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsGift()
    {
        return $this->isGift;
    }

    /**
     * Set discount price, in euros (float), positive number
     *
     * @param integer $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = abs($discount) * 100;

        return $this;
    }

    /**
     * Get discount price, in euros (float), positive number
     *
     * @return integer
     */
    public function getDiscount()
    {
        return floatval(abs($this->discount)) / 100;
    }

    /**
     * Set price for transport, in euros (float)
     *
     * @param integer $transport
     */
    public function setTransport($transport)
    {
        $this->transport = $transport * 100;

        return $this;
    }

    /**
     * Get price for transport, in euros (float)
     *
     * @return integer
     */
    public function getTransport()
    {
        return floatval($this->transport ?? 0) / 100;
    }

    /**
     * Set orderNr
     *
     * @param string $orderNr
     */
    public function setOrderNr($orderNr)
    {
        $this->orderNr = $orderNr;

        return $this;
    }

    /**
     * Get orderNr
     *
     * @return string
     */
    public function getOrderNr()
    {
        return $this->orderNr;
    }


    /**
     * Set remarks
     *
     * @param string $remarks
     */
    public function setRemarks($remarks)
    {
        $this->remarks = $remarks;

        return $this;
    }

    /**
     * Get remarks
     *
     * @return string
     */
    public function getRemarks()
    {
        return $this->remarks;
    }

    public function getOrderDate()
    {
        return $this->orderDate;
    }

    public function setOrderDate($orderDate)
    {
        $this->orderDate = $orderDate;
        return $this;
    }

    /**
     * Add productRelation
     *
     * @param ProductOrderRelation $productRelation
     */
    public function addProductRelation(ProductOrderRelation $productRelation)
    {
        $this->productRelations[] = $productRelation;

        return $this;
    }

    /**
     * Remove productRelation
     *
     * @param ProductOrderRelation $productRelation
     */
    public function removeProductRelation(ProductOrderRelation $productRelation)
    {
        $this->productRelations->removeElement($productRelation);
    }

    /**
     * Get productRelations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductRelations()
    {
        return $this->productRelations;
    }

    /**
     * Get productRelation by product id
     *
     * @return ProductOrderRelation
     */
    public function getProductRelation($productId)
    {
        return $this->productRelations->filter(function (ProductOrderRelation $r) use ($productId) {
            return $r->getProduct()->getId() == $productId;
        })->first();
    }

    /**
     * Set status
     *
     * @param OrderStatus $status
     */
    public function setStatus(OrderStatus $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return OrderStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Total actual price of this order
     * Standard prices are NOT involved, only order prices
     *
     * @return integer
     */
    public function calculateTotalPrice()
    {
        $price = 0;

        if ($this->getIsGift() == true)
            return $price;

        foreach ($this->getProductRelations() as $r)
        {
            /** @var $r ProductOrderRelation */
            $price += $r->getPrice() * $r->getQuantity();

            foreach ($r->getServices() as $s)
            {
                if (is_a($s, SalesService::class) && $s->getStatus() != SalesService::STATUS_CANCEL)
                {
                    /** @var $s SalesService */
                    $price += $s->getPrice();
                }
            }
        }

        if ($this->getDiscount() > 0)
            $price -= $this->getDiscount();

        if ($this->getTransport() > 0)
            $price += $this->getTransport();

        return $price;
    }

    /**
     * @return string for use in views tooltips
     */
    public function getAttributesList() {

        $list = array();

        foreach ($this->productRelations as $r) {

            $list[] = $r->getQuantity() . "x " . $r->getProduct()->getName();
        }

        $listStr = implode("<br/>", $list);

        if ($this->remarks)
        {
            $listStr = $this->remarks . "<br/><br/>" . $listStr;
        }

        return $listStr;
    }

    /**
     * @return integer[] key is product type name, value is quantity of that
     */
    public function getProductTypeQuantities() {

        $result = array();

        foreach ($this->productRelations as $relation) {
            
            if ($relation->getProduct()->getType()) 
                $type = $relation->getProduct()->getType()->getName();
            else
                $type = "(unknown)";

            if (!array_key_exists($type, $result))
                $result[$type] = 0;

            $result[$type] += $relation->getQuantity();   
        }

        return $result;
    }
}
