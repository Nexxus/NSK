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

/**
 * Attribute
 *
 * @ORM\Table(name="attribute")
 * @ORM\Entity(repositoryClass="TrackBundle\Repository\AttributeRepository")
 */
class Attribute
{
    const TYPE_TEXT = 0;
    const TYPE_SELECT = 1;
    const TYPE_FILE = 2;
    const TYPE_PRODUCT = 3;

    public function __construct() {
        $this->productRelations = new ArrayCollection();
        $this->productTypes = new ArrayCollection();
        $this->options = new ArrayCollection();
    }

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=11, unique=false)
     */
    private $attr_code;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @
     */
    private $name;

    /**
     * @var int Sales price per unit (to multiply by quantity)
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $price = 0;

    /**
     * @var int Use constants
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $type;

    /**
     * @var ArrayCollection|ProductType[] Which product types can use this attribute
     *
     * @ORM\ManyToMany(targetEntity="ProductType", mappedBy="attributes", fetch="LAZY")
     */
    private $productTypes;

    /**
     * @var ArrayCollection|ProductAttributeRelation[] Which products have this attribute
     *
     * @ORM\OneToMany(targetEntity="ProductAttributeRelation", mappedBy="attribute", fetch="LAZY", cascade={"all"})
     */
    private $productRelations;

    /**
     * @var ArrayCollection|AttributeOption[] To use when type is SELECT
     *
     * @ORM\OneToMany(targetEntity="AttributeOption", mappedBy="attribute", fetch="EAGER", cascade={"all"})
     */
    private $options;

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
     * Set attrCode
     *
     * @param string $attrCode
     *
     * @return Attribute
     */
    public function setAttrCode($attrCode)
    {
        $this->attr_code = $attrCode;

        return $this;
    }

    /**
     * Get attrCode
     *
     * @return string
     */
    public function getAttrCode()
    {
        return $this->attr_code;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Attribute
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
     * Set price
     *
     * @param integer $price
     *
     * @return Attribute
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return Attribute
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add productType
     *
     * @param ProductType $productType
     *
     * @return Attribute
     */
    public function addProductType(ProductType $productType)
    {
        $this->productTypes[] = $productType;

        return $this;
    }

    /**
     * Remove productType
     *
     * @param ProductType $productType
     */
    public function removeProductType(ProductType $productType)
    {
        $this->productTypes->removeElement($productType);
    }

    /**
     * Get productTypes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductTypes()
    {
        return $this->productTypes;
    }

    /**
     * Add productRelation
     *
     * @param ProductAttributeRelation $productRelation
     *
     * @return Attribute
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
     * Add option
     *
     * @param AttributeOption $option
     *
     * @return Attribute
     */
    public function addOption(AttributeOption $option)
    {
        $this->options[] = $option;

        return $this;
    }

    /**
     * Remove option
     *
     * @param AttributeOption $option
     */
    public function removeOption(AttributeOption $option)
    {
        $this->options->removeElement($option);
    }

    /**
     * Get options
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOptions()
    {
        return $this->options;
    }
}
