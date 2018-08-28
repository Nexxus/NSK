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
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="TrackBundle\Repository\ProductRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"p" = "Product", "s" = "Service"})
 */
class Product
{
    public function __construct() {
        $this->attributeRelations = new ArrayCollection();
        $this->orderRelations = new ArrayCollection();
        $this->services = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }
    
    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->updatedAt= new \DateTime();
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
     * @ORM\Column(type="string", length=16, unique=true)
     */
    private $sku;

    /**
     * @var int
     * 
     * @ORM\Column(type="integer")
     */
    private $quantity;
    
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var ProductType
     *
     * @ORM\ManyToOne(targetEntity="ProductType")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var int Price for sale, in eurocents
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $price;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default" : false})
     */
    private $isAttribute = false;

    /**
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="Location", fetch="EAGER")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=false)
     */
    private $location;

    /**
     * @var createdAt
     * 
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;
    
    /**
     * @var ArrayCollection|ProductAttributeRelation[]
     *
     * @ORM\OneToMany(targetEntity="ProductAttributeRelation", mappedBy="product", fetch="LAZY", cascade={"all"})
     */
    private $attributeRelations;

    /**
     * @var ArrayCollection|ProductOrderRelation[]
     *
     * @ORM\OneToMany(targetEntity="ProductOrderRelation", mappedBy="product", fetch="LAZY", cascade={"all"})
     */
    private $orderRelations;

    /**
     * @var ArrayCollection|ProductImage[]
     *
     * @ORM\OneToMany(targetEntity="ProductImage", mappedBy="product", fetch="LAZY", cascade={"all"})
     */
    private $images;

    /**
     * @var ArrayCollection|Service[] Services that are applied to this Product
     *
     * @ORM\OneToMany(targetEntity="Service", mappedBy="product", fetch="LAZY")
     */
    private $services;

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
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return ProductOrderRelation
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set sku
     *
     * @param string $sku
     *
     * @return Product
     */
    public function setSku($sku)
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * Get sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Product
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
     * Set description
     *
     * @param string $description
     *
     * @return Product
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

    /**
     * Set price
     *
     * @param integer $price
     *
     * @return Product
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
     * Set isAttribute
     *
     * @param bool $isAttribute
     *
     * @return Product
     */
    public function setIsAttribute($isAttribute)
    {
        $this->isAttribute = $isAttribute;

        return $this;
    }

    /**
     * Get isAttribute
     *
     * @return bool
     */
    public function getIsAttribute()
    {
        return $this->isAttribute;
    }

    /**
     * Set type
     *
     * @param ProductType $type
     *
     * @return Product
     */
    public function setType(ProductType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return ProductType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set location
     *
     * @param Location $location
     *
     * @return Product
     */
    public function setLocation(Location $location)
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

    /**
     * Add attributeRelation
     *
     * @param ProductAttributeRelation $attributeRelation
     *
     * @return Product
     */
    public function addAttributeRelation(ProductAttributeRelation $attributeRelation)
    {
        $this->attributeRelations[] = $attributeRelation;

        return $this;
    }

    /**
     * Remove attributeRelation
     *
     * @param ProductAttributeRelation $attributeRelation
     */
    public function removeAttributeRelation(ProductAttributeRelation $attributeRelation)
    {
        $this->attributeRelations->removeElement($attributeRelation);
    }

    /**
     * Get attributeRelations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAttributeRelations()
    {
        return $this->attributeRelations;
    }

    /**
     * Add orderRelation
     *
     * @param ProductOrderRelation $orderRelation
     *
     * @return Product
     */
    public function addOrderRelation(ProductOrderRelation $orderRelation)
    {
        $this->orderRelations[] = $orderRelation;

        return $this;
    }

    /**
     * Remove orderRelation
     *
     * @param ProductOrderRelation $orderRelation
     */
    public function removeOrderRelation(ProductOrderRelation $orderRelation)
    {
        $this->orderRelations->removeElement($orderRelation);
    }

    /**
     * Get orderRelations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrderRelations()
    {
        return $this->orderRelations;
    }

    /**
     * Add image
     *
     * @param ProductImage $image
     *
     * @return Product
     */
    public function addImage(ProductImage $image)
    {
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove image
     *
     * @param ProductImage $image
     */
    public function removeImage(ProductImage $image)
    {
        $this->images->removeElement($image);
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Add service
     *
     * @param Service $service
     *
     * @return Product
     */
    public function addService(Service $service)
    {
        $this->services[] = $service;

        return $this;
    }

    /**
     * Remove service
     *
     * @param Service $service
     */
    public function removeService(Service $service)
    {
        $this->services->removeElement($service);
    }

    /**
     * Get services
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getServices()
    {
        return $this->services;
    }
    
    public function getCreatedAt()
    {
        return $this->updatedAt->format('d-m-Y H:i');
    }
    
    public function getUpdatedAt()
    {
        return $this->updatedAt->format('d-m-Y H:i');
    }
}
