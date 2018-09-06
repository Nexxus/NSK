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

namespace TrackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AdminBundle\Entity\Partner;

/**
 * PurchaseOrder
 *
 * @ORM\Entity(repositoryClass="TrackBundle\Repository\PurchaseOrderRepository")
 */
class PurchaseOrder extends AOrder
{
    /**
     * @var Partner Deliverer of this order
     *
     * @ORM\ManyToOne(targetEntity="AdminBundle\Entity\Partner", fetch="EAGER")
     * @ORM\JoinColumn(name="supplier_id", referencedColumnName="id")
     */
    private $supplier;

    /**
     * @return PurchaseOrder
     */
    public function setSupplier(Partner $partner)
    {
        $this->supplier = $partner;

        return $this;
    }

    /**
     * @return Partner
     */
    public function getSupplier()
    {
        return $this->supplier;
    }
}