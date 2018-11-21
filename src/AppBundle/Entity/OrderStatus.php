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
 * OrderStatus
 *
 * @ORM\Table(name="order_status")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OrderStatusRepository")
 */
class OrderStatus extends AStatus
{
    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isSale;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isPurchase;

    /**
     * @param bool $isSale
     * @return OrderStatus
     */
    public function setIsSale($isSale)
    {
        $this->isSale = $isSale;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsSale()
    {
        return $this->isSale;
    }

    /**
     * @param bool $isPurchase
     * @return OrderStatus
     */
    public function setIsPurchase($isPurchase)
    {
        $this->isPurchase = $isPurchase;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsPurchase()
    {
        return $this->isPurchase;
    }
}
