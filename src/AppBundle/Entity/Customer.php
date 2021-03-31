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
use AppBundle\Entity\SalesOrder;

/**
 * Customer
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CustomerRepository")
 */
class Customer extends ACompany
{
    public function __construct() {
        $this->orders = new ArrayCollection();
    }

    const PARTNER = 1;
    const OWNING_PARTNER = 2;
    const HAS_PARTNER = -1;

    /**
     * @var int Use constants
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $isPartner;

    /**
     * @var ArrayCollection|SalesOrder[] Sales orders that this customer bought
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\SalesOrder", mappedBy="customer", cascade={"persist"})
     */
    private $orders;


    /**
     * @param int $isPartner Use constants
     *
     * @return Customer
     */
    public function setIsPartner($isPartner)
    {
        $this->isPartner = $isPartner;

        return $this;
    }

    /**
     * @return int
     */
    public function getIsPartner()
    {
        return $this->isPartner;
    }


    /**
     * @param SalesOrder $salesOrder
     * @return Customer
     */
    public function addSalesOrder(SalesOrder $salesOrder)
    {
        $this->orders[] = $salesOrder;

        return $this;
    }

    /**
     * @param SalesOrder $salesOrder
     */
    public function removeSalesOrder(SalesOrder $salesOrder)
    {
        $this->orders->removeElement($salesOrder);
    }

    /**
     * @return ArrayCollection|SalesOrder[]
     */
    public function getSalesOrders()
    {
        return $this->orders;
    }
}

