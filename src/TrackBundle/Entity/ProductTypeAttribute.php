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

/**
 * ProductTypeAttribute
 * 
 * This determines which product types get 
 * which attributes by default
 *
 * @ORM\Table(name="product_type_attribute")
 * @ORM\Entity(repositoryClass="TrackBundle\Repository\ProductTypeAttributeRepository")
 */
class ProductTypeAttribute
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="type_id", type="integer")
     */
    private $typeId;

    /**
     * @var int
     * @ORM\ManyToOne(targetEntity="Attribute")
     * @ORM\JoinColumn(name="attr_id", referencedColumnName="id")
     */
    private $attrId = 1;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set typeId
     *
     * @param integer $typeId
     *
     * @return ProductTypeAttribute
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * Get typeId
     *
     * @return int
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * Set attrId
     *
     * @param integer $attrId
     *
     * @return ProductTypeAttribute
     */
    public function setAttrId($attrId)
    {
        $this->attrId = $attrId;

        return $this;
    }

    /**
     * Get attrId
     *
     * @return int
     */
    public function getAttrId()
    {
        return $this->attrId;
    }
}

