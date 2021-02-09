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

/**
 * @ORM\Table(name="aservice")
 * @ORM\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"r" = "SalesService", "t" = "TaskService"})
 */
abstract class AService
{
    public function __construct(ProductOrderRelation $productOrderRelation) {
        $this->productOrderRelation = $productOrderRelation;
        $productOrderRelation->addService($this);
        $this->status = self::STATUS_TODO;
    }

    const STATUS_TODO = 0;
    const STATUS_HOLD = 1;
    const STATUS_BUSY = 2;
    const STATUS_DONE = 3;
    const STATUS_CANCEL = 4;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int Use constants
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $status;

    /**
     * @var ProductOrderRelation
     *
     * @ORM\ManyToOne(targetEntity="ProductOrderRelation", inversedBy="services")
     * @ORM\JoinColumn(name="relation_id", referencedColumnName="id"),
     */
    protected $productOrderRelation;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

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
     * Set status
     *
     * @param integer $status
     *
     * @return AService
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function getProductOrderRelation()
    {
        return $this->productOrderRelation;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return AService
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
