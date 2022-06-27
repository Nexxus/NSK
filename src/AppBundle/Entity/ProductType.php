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
 * ProductType
 *
 * Types bound to products by ID
 *
 * @ORM\Table(name="product_type")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductTypeRepository")
 */
class ProductType
{
    public function __construct() {
        $this->attributes = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->tasks = new ArrayCollection();
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serialize\Groups({"api:purchaseorders"})
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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @Serialize\Groups({"api:purchaseorders", "vue:products"})
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="pindex", type="integer", nullable=true)
     * @Serialize\Groups({"api:purchaseorders"})
     */
    private $pindex;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     * @Serialize\Groups({"api:purchaseorders"})
     */
    private $comment = null;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default" : false})
     * @Serialize\Groups({"api:purchaseorders"})
     */
    private $isAttribute = false;

    /**
     * @var ArrayCollection|Attribute[] Which attributes should be selectable for products of this type
     *
     * @ORM\ManyToMany(targetEntity="Attribute", inversedBy="productTypes")
     * @ORM\JoinTable(name="product_type_attribute")
     */
    private $attributes;

    /**
     * @var ArrayCollection|Task[] Which tasks should be selectable for products of this type
     *
     * @ORM\ManyToMany(targetEntity="Task", inversedBy="productTypes")
     * @ORM\JoinTable(name="product_type_task")
     * @Serialize\Groups({"vue:products"})
     */
    private $tasks;

    /**
     * @var ArrayCollection|Product[]
     *
     * @ORM\OneToMany(targetEntity="Product", mappedBy="type")
     */
    private $products;

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
     * @return ProductType
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
     * Set name
     *
     * @param string $name
     *
     * @return ProductType
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
     * Set pindex
     *
     * @param integer $pindex
     *
     * @return ProductType
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
     * Set comment
     *
     * @param string $comment
     *
     * @return ProductType
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Add attribute
     *
     * @param Attribute $attribute
     *
     * @return ProductType
     */
    public function addAttribute(Attribute $attribute)
    {
        $this->attributes[] = $attribute;

        return $this;
    }

    /**
     * Remove attribute
     *
     * @param Attribute $attribute
     */
    public function removeAttribute(Attribute $attribute)
    {
        $this->attributes->removeElement($attribute);
    }

    /**
     * Get attributes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Add task
     *
     * @param Task $task
     *
     * @return ProductType
     */
    public function addTask(Task $task)
    {
        $this->tasks[] = $task;

        return $this;
    }

    /**
     * Remove task
     *
     * @param Task $task
     */
    public function removeTask(Task $task)
    {
        $this->tasks->removeElement($task);
    }

    /**
     * Get tasks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * Add product
     *
     * @param Product $product
     *
     * @return ProductType
     */
    public function addProduct(Product $product)
    {
        $this->products[] = $product;

        return $this;
    }

    /**
     * Remove product
     *
     * @param Product $product
     */
    public function removeProduct(Product $product)
    {
        $this->products->removeElement($product);
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducts()
    {
        return $this->products;
    }


    /**
     * Set isAttribute
     *
     * @param bool $isAttribute
     *
     * @return ProductType
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

    public function __toString() {
        return $this->getName();
    }
}
