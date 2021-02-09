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
use AppBundle\Entity\Customer;

/**
 * SalesOrder
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SalesOrderRepository")
 */
class SalesOrder extends AOrder
{
    const DELIVERYTYPE_PICKUP = 0;
    const DELIVERYTYPE_DELIVERY = 1;
    const DELIVERYTYPE_SHIP = 2;
    
    /**
     * @var int Use constants
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $deliveryType;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deliveryDate;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $deliveryInstructions;
    
    /**
     * @var DeliveryImageFile For future use
     *
     * @ORM\OneToOne(targetEntity="DeliveryImageFile", mappedBy="salesOrder", cascade={"all"}, orphanRemoval=true)
     */
    private $deliveryImage;    

    /**
     * @var Customer Buyer of this order
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Customer")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     */
    private $customer;

    /**
     * @ORM\OneToOne(targetEntity="PurchaseOrder")
     * @ORM\JoinColumn(name="backingPurchaseOrder_id", referencedColumnName="id", nullable=true)
     */
    private $backingPurchaseOrder;

    /**
     * @ORM\OneToOne(targetEntity="Repair", mappedBy="order", cascade={"all"}, orphanRemoval=true)
     */
    private $repair;

    public function getDeliveryDate()
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate($deliveryDate)
    {
        $this->deliveryDate = $deliveryDate;
        return $this;
    }

    public function getDeliveryType()
    {
        return $this->deliveryType;
    }

    public function setDeliveryType($deliveryType)
    {
        $this->deliveryType = $deliveryType;
        return $this;
    }

    public function setDeliveryInstructions($deliveryInstructions)
    {
        $this->deliveryInstructions = $deliveryInstructions;
        return $this;
    }

    public function getDeliveryInstructions()
    {
        return $this->deliveryInstructions;
    }
    
    public function setDeliveryImage(DeliveryImageFile $deliveryImage)
    {
        $this->deliveryImage = $deliveryImage;

        return $this;
    }

    public function getDeliveryImage()
    {
        return $this->deliveryImage;
    }    

    /**
     * @return SalesOrder
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * When sales is before purchase, this property should be set
     *
     * @return SalesOrder
     */
    public function setBackingPurchaseOrder(PurchaseOrder $backingPurchaseOrder)
    {
        $this->backingPurchaseOrder = $backingPurchaseOrder;

        return $this;
    }

    /**
     * When sales is before purchase
     *
     * @return PurchaseOrder
     */
    public function getBackingPurchaseOrder()
    {
        return $this->backingPurchaseOrder;
    }

    /**
     * @return SalesOrder
     */
    public function setRepair(Repair $repair)
    {
        $this->repair = $repair;

        return $this;
    }

    /**
     * @return Repair
     */
    public function getRepair()
    {
        return $this->repair;
    }
}