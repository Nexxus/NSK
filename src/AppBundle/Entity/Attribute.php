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
use JMS\Serializer\Annotation as Serialize;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Attribute
 *
 * @ORM\Table(name="attribute")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AttributeRepository")
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
     * @Serialize\Groups({"api:purchaseorders", "product:edit"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Serialize\Groups({"api:purchaseorders"})
     */
    private $externalId;      

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=11, unique=false)
     * @Serialize\Groups({"api:purchaseorders"})
     */
    private $attr_code;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @Serialize\Groups({"api:purchaseorders", "product:edit"})
     * @
     */
    private $name;

    /**
     * @var int Standard sales price, in eurocents, to use if type is open text field
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Serialize\Groups({"api:purchaseorders"})
     */
    private $price;

    /**
     * @var int Use constants
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Serialize\Groups({"api:purchaseorders", "product:edit"})
     */
    private $type;

    /**
     * @var ArrayCollection|ProductType[] Which product types can use this attribute
     * @ORM\ManyToMany(targetEntity="ProductType", mappedBy="attributes")
     */
    private $productTypes;

    /**
     * @var ProductType If attribute type is Product, this property filters the possible products
     * @ORM\ManyToOne(targetEntity="ProductType")
     * @ORM\JoinColumn(name="product_type_filter_id", referencedColumnName="id")
     */
    private $productTypeFilter;

    /**
     * @var ArrayCollection|ProductAttributeRelation[] Which products have this attribute
     * @ORM\OneToMany(targetEntity="ProductAttributeRelation", mappedBy="attribute", cascade={"all"}, orphanRemoval=true)
     */
    private $productRelations;

    /**
     * @var ArrayCollection|AttributeOption[] To use when type is SELECT
     *
     * @ORM\OneToMany(targetEntity="AttributeOption", mappedBy="attribute", cascade={"all"})
     * @Serialize\Groups({"api:purchaseorders", "product:edit"})
     */
    private $options;

    /**
     * @var bool If true, the ProductAttributeRelation can use quantity, meaning quantity per unit
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default" : false})
     * @Serialize\Groups({"api:purchaseorders", "product:edit"})
     */
    private $hasQuantity = false;

    /**
     * @var bool If true, the attribute can be published to webshop
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default" : true})
     * @Serialize\Groups({"api:purchaseorders"})
     */
    private $isPublic = true;    

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
     * Set external id like from webshop
     *
     * @param integer $name
     *
     * @return Attribute
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * Set external id like from webshop
     *
     * @return int
     */
    public function getExternalId()
    {
        return $this->externalId;
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
     * Set productTypeFilter
     *
     * @param string $attrCode
     *
     * @return Attribute
     */
    public function setProductTypeFilter(ProductType $productType)
    {
        if ($this->type != $this::TYPE_PRODUCT)
            throw new \Exception("ProductTypeFilter can only be set when Attribute is of type Product.");

        $this->productTypeFilter = $productType;

        return $this;
    }


    /**
     * Get productTypeFilter
     *
     * @return ProductType
     */
    public function getProductTypeFilter()
    {
        if ($this->type != $this::TYPE_PRODUCT)
            throw new \Exception("ProductTypeFilter can only exist when Attribute is of type Product.");

        return $this->productTypeFilter;
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
     * Set standard sales price, in euros (float), to use if type is open text field
     *
     * @param float $price
     *
     * @return Attribute
     */
    public function setPrice($price)
    {
        if ($this->type == $this::TYPE_PRODUCT)
            throw new \Exception("Price should be registered in product details.");
        elseif ($this->type == $this::TYPE_SELECT)
            throw new \Exception("Price should be registered per selectable option.");

        $this->price = $price * 100;

        return $this;
    }

    /**
     * Get standard sales price, in euros (float), to use if type is open text field
     *
     * @return float
     */
    public function getPrice()
    {
        if ($this->type == $this::TYPE_PRODUCT)
            throw new \Exception("Price should be registered in product details.");
        elseif ($this->type == $this::TYPE_SELECT)
            throw new \Exception("Price should be registered per selectable option.");

        return floatval($this->price) / 100;
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
        $productType->addAttribute($this);
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
        $productType->removeAttribute($this);
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
        if ($this->getType() != self::TYPE_SELECT)
            throw new \Exception("Options is only available if attribute is of select type.");

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
        if ($this->getType() != self::TYPE_SELECT)
            throw new \Exception("Options is only available if attribute is of select type.");

        $this->options->removeElement($option);
    }

    /**
     * Get options
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOptions()
    {
        if ($this->getType() != self::TYPE_SELECT)
            throw new \Exception("Options is only available if attribute is of select type.");

        return $this->options;
    }

    /**
     * Set hasQuantity
     *
     * @param bool $hasQuantity
     *
     * @return Attribute
     */
    public function setHasQuantity($hasQuantity)
    {
        $this->hasQuantity = $hasQuantity;

        return $this;
    }

    /**
     * Get hasQuantity
     *
     * @return bool
     */
    public function getHasQuantity()
    {
        return $this->hasQuantity;
    }

    /**
     * @param bool $isPublic
     *
     * @return Attribute
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }    

    public function __toString()
    {
        return $this->getName();
    }
}
