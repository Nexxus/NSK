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
     * @Serialize\Groups({"api:purchaseorders", "product:index", "product:edit", "product:attributable"})
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
     * @ORM\Column(type="string", length=16)
     * @Serialize\Groups({"api:purchaseorders", "product:index", "product:edit"})
     */
    private $sku;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Serialize\Groups({"api:purchaseorders", "product:index", "product:edit", "product:attributable"})
     */
    private $name;

    /**
     * @var ProductType
     *
     * @ORM\ManyToOne(targetEntity="ProductType")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     * @Serialize\Groups({"api:purchaseorders", "product:index", "product:edit"})
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     * @Serialize\Groups({"api:purchaseorders", "product:edit"})
     */
    private $description;

    /**
     * @var int Standard sales price, in eurocents, per unit
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Serialize\Groups({"api:purchaseorders", "product:index", "product:edit"})
     */
    private $price;

    /**
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="Location", inversedBy="products")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=false)
     * @Serialize\Groups({"api:purchaseorders", "product:index", "product:edit"})
     */
    private $location;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @Serialize\Groups({"api:purchaseorders"})
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @Serialize\Groups({"api:purchaseorders"})
     */
    protected $updatedAt;

    /**
     * @var ProductStatus
     *
     * @ORM\ManyToOne(targetEntity="ProductStatus", fetch="EAGER")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * @Serialize\Groups({"api:purchaseorders", "product:edit"})
     */
    private $status;

    /**
     * @var ArrayCollection|ProductAttributeRelation[]
     * @ORM\OneToMany(targetEntity="ProductAttributeRelation", mappedBy="product", cascade={"all"}, orphanRemoval=true)
     * @Serialize\Groups({"api:purchaseorders", "product:edit"})
     */
    private $attributeRelations;

    /**
     * Collection of relations to attributes in which this product was an attributed product
     * KEEP THIS PROPERTY PRIVATE
     *
     * @var ArrayCollection|ProductAttributeRelation[]
     * @ORM\OneToMany(targetEntity="ProductAttributeRelation", mappedBy="valueProduct", cascade={"all"}, orphanRemoval=true)
     */
    private $attributedRelations;

    /**
     * @var ArrayCollection|ProductOrderRelation[]
     * @ORM\OneToMany(targetEntity="ProductOrderRelation", mappedBy="product", fetch="EAGER", cascade={"all"}, orphanRemoval=true)
     */
    private $orderRelations;

    /**
     * @var Supplier
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Supplier")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     * @Serialize\Groups({"api:purchaseorders"})
     */
    private $owner;

    /**
     * @var ArrayCollection|ProductAttributeFile[]
     *
     * @ORM\OneToMany(targetEntity="ProductAttributeFile", mappedBy="product")
     * @Serialize\Groups({"api:purchaseorders"})
     */
    private $files;

    /**
     * @var Stock
     * 
     * @ORM\OneToOne(targetEntity="Stock", mappedBy="product")
     * @Serialize\Groups({"api:purchaseorders", "product:index"})
     */
    private $stock;    

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
     * Set external id like from webshop
     *
     * @param integer $name
     *
     * @return Product
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

    public function getStock()
    {
        return $this->stock;
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

    /**
     * @Serialize\VirtualProperty()
     * @Serialize\Groups({"product:edit"})
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
     * @Serialize\VirtualProperty()
     * @Serialize\Groups({"product:edit"})      
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
     * 
     * @Serialize\VirtualProperty()
     * @Serialize\Groups({"product:edit"})        
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

    /**
     * @Serialize\VirtualProperty()
     * @Serialize\Groups({"product:index"})           
     * @return string proxy which will be filled with Vue and Axios
     */
    public function getServicesDone() {
        return '...';
    }

    /**
     * @Serialize\VirtualProperty()
     * @Serialize\Groups({"product:index"})           
     * @return string for use in views tooltips
     */
    public function getTasksCount() {
        if ($this->type && $this->getType()->getTasks()->count())
            return $this->getType()->getTasks()->count();
        return 0;
    }
    
    /**
     * @Serialize\VirtualProperty()
     * @Serialize\Groups({"product:index"})           
     * @return string for use in views tooltips
     */
    public function getAttributesList() {

        $list = array();

        foreach ($this->attributeRelations as $r) {

            if ($r->getAttribute()->getType() == Attribute::TYPE_FILE && $r->getFiles()->count()) {

                $filesList = array();
                foreach ($r->getFiles() as $f) {

                    $filesList[] = $f->getOriginalClientFilename();
                }

                $list[] = $r->getAttribute()->getName() . ": " . implode(", ", $filesList);
            }
            elseif ($r->getAttribute()->getType() == Attribute::TYPE_PRODUCT && $r->getValueProduct()) {
                
                $list[] = $r->getAttribute()->getName() . ": " . $r->getValueProduct()->getName();
            }
            elseif ($r->getAttribute()->getType() == Attribute::TYPE_SELECT && $r->getValue()) {
                
                if (!$r->getSelectedOption())
                    $selectedOption = "(none)";
                else
                    $selectedOption = $r->getSelectedOption()->getName();
                
                $list[] = $r->getAttribute()->getName() . ": " . $selectedOption;
            }
            elseif ($r->getValue()) {
                
                $list[] = $r->getAttribute()->getName() . ": " . $r->getValue();
            }
        }

        return implode("<br />", $list);
    }
}
