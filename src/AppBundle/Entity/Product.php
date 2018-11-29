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
use JMS\Serializer\Annotation as JMS;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use AppBundle\Entity\Supplier;

/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductRepository")
 */
class Product
{
    public function __construct() {
        $this->attributeRelations = new ArrayCollection();
        $this->attributedRelations = new ArrayCollection();
        $this->orderRelations = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->files = new ArrayCollection();
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

    #region Properties

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
     * @ORM\Column(type="string", length=16)
     */
    private $sku;

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
     * @var int Standard sales price, in eurocents, per unit
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $price;

    /**
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="Location", inversedBy="products", fetch="EAGER")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=false)
     */
    private $location;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * @var ProductStatus
     *
     * @ORM\ManyToOne(targetEntity="ProductStatus", fetch="EAGER")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    private $status;

    /**
     * @var ArrayCollection|ProductAttributeRelation[]
     * @ORM\OneToMany(targetEntity="ProductAttributeRelation", mappedBy="product", fetch="LAZY", cascade={"all"}, orphanRemoval=true)
     */
    private $attributeRelations;

    /**
     * Collection of relations to attributes in which this product was an attributed product
     * KEEP THIS PROPERTY PRIVATE
     *
     * @var ArrayCollection|ProductAttributeRelation[]
     * @ORM\OneToMany(targetEntity="ProductAttributeRelation", mappedBy="valueProduct", fetch="LAZY", cascade={"all"}, orphanRemoval=true)
     * @JMS\Exclude
     */
    private $attributedRelations;

    /**
     * @var ArrayCollection|ProductOrderRelation[]
     * @ORM\OneToMany(targetEntity="ProductOrderRelation", mappedBy="product", fetch="EAGER", cascade={"all"}, orphanRemoval=true)
     * @JMS\Exclude
     */
    private $orderRelations;

    /**
     * @var Supplier
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Supplier", fetch="EAGER")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     */
    private $owner;

    /**
     * @var ArrayCollection|ProductAttributeFile[]
     *
     * @ORM\OneToMany(targetEntity="ProductAttributeFile", mappedBy="product", fetch="EAGER")
     */
    private $files;

    #endregion

    #region Getters and setters

    /**
     * Returns all files of all attributes. Files can be attached to products thru its attributes.
     * @return ProductAttributeFile[]|ArrayCollection
     */
    public function getFiles()
    {
        return $this->files;
    }

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
     * Set standard sales price, in euros (float), per unit
     *
     * @param float $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price * 100;

        return $this;
    }

    /**
     * Get standard sales price, in euros (float), per unit
     *
     * @return float
     */
    public function getPrice()
    {
        return floatval($this->price) / 100;
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
     * Set status
     *
     * @param ProductStatus $status
     *
     * @return Product
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return ProductStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return Product
     */
    public function setOwner(Supplier $supplier)
    {
        $this->owner = $supplier;

        return $this;
    }

    /**
     * @return Supplier
     */
    public function getOwner()
    {
        return $this->owner;
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

    public function getCreatedAt()
    {
        return $this->updatedAt->format('d-m-Y H:i');
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt->format('d-m-Y H:i');
    }

    #endregion

    #region Quantity calculators

    public function getQuantityPurchased()
    {
        $r = $this->getPurchaseOrderRelation();
        return $r ? $r->getQuantity() : 0;
    }

    public function getQuantityInStock()
    {
        $isStock = $this->getStatus() ? $this->getStatus()->getIsStock() : true;
        if (!$isStock)
        {
            $q = 0;
        }
        elseif ($r = $this->getPurchaseOrderRelation())
        {
            $q = $r->getQuantity();
        }
        elseif ($this->getSalesOrderRelations()->count() == 1 && $this->getSalesOrderRelations()->first()->getOrder()->getRepair())
        {
            // Repair
            $r = $this->getSalesOrderRelations()->first();
            $q = $r->getQuantity();
        }
        else
        {
            //throw new \Exception("Product has no purchase order and is not a repair, which should be impossible.");
            $q = 0;
        }

        return $q - $this->getQuantitySold();
    }

    public function getQuantityOnHold()
    {
        $q = $this->getQuantityInStock() - $this->getQuantitySaleable();
        return $q > 0 ? $q : 0;
    }

    public function getQuantitySaleable()
    {
        $isSaleable = $this->getStatus() ? $this->getStatus()->getIsSaleable() : false;
        if (!$isSaleable)
        {
            $q = 0;
        }
        else
        {
            $r = $this->getPurchaseOrderRelation();
            $q = $r ? $r->getQuantity() : 0;
        }

        return $q - $this->getQuantitySold();
    }

    public function getQuantitySold()
    {
        $isSaleable = $this->getStatus() ? $this->getStatus()->getIsSaleable() : false;

        if (!$isSaleable)
            return 0;

        $q = 0;

        foreach ($this->getSalesOrderRelations() as $r)
        {
            $q += $r->getQuantity();
        }

        return $q;
    }

    #endregion

    /**
     * @return ProductOrderRelation Relation to purchase order
     */
    public function getPurchaseOrderRelation()
    {
        return $this->getOrderRelations()->filter(
            function($r) {
                /** @var $r ProductOrderRelation */
                return is_a($r->getOrder(), PurchaseOrder::class);
            })->first();
    }

    /**
     * @return Collection|ProductOrderRelation[] Relations to sales orders
     */
    public function getSalesOrderRelations()
    {
        return $this->getOrderRelations()->filter(
            function($r) {
                /** @var $r ProductOrderRelation */
                return is_a($r->getOrder(), SalesOrder::class);
            });
    }

    /**
     * Standard prices multiplied by Quantities of (selected) attributes and/or attributed products
     * @return double
     */
    public function getTotalStandardPriceOfAttributes()
    {
        $price = 0;

        foreach ($this->getAttributeRelations() as $r)
        {
            $price += $r->getTotalStandardPrice();
        }

        return $price;
    }
}
