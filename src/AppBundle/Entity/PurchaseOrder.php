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
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use AppBundle\Entity\Supplier;

/**
 * PurchaseOrder
 *
  * @ORM\Entity(repositoryClass="AppBundle\Repository\PurchaseOrderRepository")
 */
class PurchaseOrder extends AOrder
{
    /**
     * @var Supplier Deliverer of this order
     *
     * @Assert\Valid
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Supplier", fetch="EAGER")
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id")
     * @JMS\MaxDepth(depth=1)
     */
    private $supplier;

    /**
     * @ORM\OneToOne(targetEntity="Pickup", mappedBy="order")
     */
    private $pickup;

    /**
     * @return PurchaseOrder
     */
    public function setSupplier(Supplier $supplier)
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * @return Supplier
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @return PurchaseOrder
     */
    public function setPickup(Pickup $pickup)
    {
        $this->pickup = $pickup;

        return $this;
    }

    /**
     * @return Pickup
     */
    public function getPickup()
    {
        return $this->pickup;
    }
}
