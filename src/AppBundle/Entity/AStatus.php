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
 * @ORM\MappedSuperclass
 */
abstract class AStatus
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(name="pindex", type="integer", nullable=true)
     */
    protected $pindex;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $isStock;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $isStockSaleable;

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
     * Set pindex
     *
     * @param integer $pindex
     */
    public function setPindex($pindex)
    {
        $this->pindex = $pindex;

        return $this;
    }

    /**
     * Get pindex
     *
     * @return integer
     */
    public function getPindex()
    {
        return $this->pindex;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param bool $isStock
     */
    public function setIsStock($isStock)
    {
        $this->isStock = $isStock;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsStock()
    {
        if ($this->isStock === null)
        {
            if (get_class($this) == \AppBundle\Entity\OrderStatus::class && !$this->getIsPurchase())
            {
                return false;
            }
            else
            {
                return true;
            }
        }

        return $this->isStock;
    }

    /**
     * @param bool $isStockSaleable
     */
    public function setIsStockSaleable($isStockSaleable)
    {
        $this->isStockSaleable = $isStockSaleable;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsStockSaleable()
    {
        if ($this->isStockSaleable === null)
        {
            if (get_class($this) == \AppBundle\Entity\OrderStatus::class && !$this->getIsPurchase())
            {
                return false;
            }
            else
            {
                return true;
            }
        }

        return $this->isStockSaleable;
    }
}