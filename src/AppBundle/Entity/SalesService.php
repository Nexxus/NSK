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
 * Copiatek � info@copiatek.nl � Postbus 547 2501 CM Den Haag
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SalesService is a task that has been applied to a product which is not purchased,
 * but owned by customer. A repair is a consumed service.
 *
 * @ORM\Entity
 */
class SalesService extends AService
{
    public function __construct(ProductOrderRelation $productOrderRelation)
    {
        $order = $productOrderRelation->getOrder();
        $orderClass = get_class($order);

        if ($order == null || $orderClass != SalesOrder::class)
            throw new \Exception("Repair service can only be bound to sales order");

        parent::__construct($productOrderRelation);
    }

    /**
     * @var int In eurocents
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $price;

    /**
     * In euros (float)
     *
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price * 100;

        return $this;
    }

    /**
     * In euros (float)
     *
     * @return float
     */
    public function getPrice()
    {
        return floatval($this->price) / 100;
    }

    /**
     * @return SalesOrder
     */
    public function getSalesOrder() {
        return $this->productOrderRelation->getOrder();
    }
}
