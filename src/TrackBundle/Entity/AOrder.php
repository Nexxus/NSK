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
use Doctrine\Common\Collections\ArrayCollection;
use TrackBundle\Entity\Location;

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
     * @var ArrayCollection|ProductOrderRelation[] Which products have this attribute
     *
     * @ORM\OneToMany(targetEntity="ProductOrderRelation", mappedBy="order", cascade={"all"})
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
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="TrackBundle\Entity\Location", inversedBy="orders")
     * @ORM\JoinColumn(name="location", referencedColumnName="id")
     */
    protected $location;

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
     * Set orderNr
     *
     * @param string $orderNr
     *
     * @return AOrder
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
     * Add productRelation
     *
     * @param ProductAttributeRelation $productRelation
     *
     * @return AOrder
     */
    public function addProductRelation(ProductAttributeRelation $productRelation)
    {
        $this->productRelations[] = $productRelation;

        return $this;
    }

    /**
     * Remove productRelation
     *
     * @param ProductAttributeRelation $productRelation
     */
    public function removeProductRelation(ProductAttributeRelation $productRelation)
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
     * Set status
     *
     * @param OrderStatus $status
     *
     * @return AOrder
     */
    public function setStatus(OrderStatus $status = null)
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
     * Set location
     *
     * @param Location $location
     *
     * @return AOrder
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

}
