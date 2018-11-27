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

/**
 * RepairService is a task that has been applied to a product which is not purchased,
 * but owned by customer. A repair is a consumed service.
 *
 * @ORM\Entity
 */
class RepairService extends AService
{
    public function __construct(ProductOrderRelation $productOrderRelation)
    {
        $order = $productOrderRelation->getOrder();
        $orderClass = get_class($order);

        if ($order == null || $orderClass != SalesOrder::class || $order->getRepair() == null)
            throw new \Exception("Repair service can only be bound to sales order with repair property");

        parent::__construct($productOrderRelation);
    }

    /**
     * @return SalesOrder
     */
    public function getSalesOrder() {
        return $this->productOrderRelation->getOrder();
    }

    /**
     * @return Repair
     */
    public function getRepair() {
        return $this->getSalesOrder()->getRepair();
    }
}
