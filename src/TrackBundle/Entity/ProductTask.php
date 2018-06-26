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
 * ProductTask
 *
 * @ORM\Table(name="product_task")
 * @ORM\Entity(repositoryClass="TrackBundle\Repository\ProductTaskRepository")
 */
class ProductTask
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
     *
     * @ORM\ManyToOne(targetEntity="Product") 
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $productId;

    /**
     * @var int
     *
     * @ORM\Column(name="attribute_id", type="integer")
     */
    private $attributeId;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=true)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=500, nullable=true)
     */
    private $comment;


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
     * Set productId
     *
     * @param integer $productId
     *
     * @return ProductTask
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * Get productId
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Set attrId
     *
     * @param integer $attrId
     *
     * @return ProductTask
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

    /**
     * Set value
     *
     * @param string $value
     *
     * @return ProductTask
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return ProductTask
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
}

