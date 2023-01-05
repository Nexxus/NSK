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
 * AttributeOption
 *
 * @ORM\Table(name="attribute_option")
 * @ORM\Entity
 */
class AttributeOption
{
    public function __construct(Attribute $attribute) {
        $this->attribute = $attribute;
        $attribute->addOption($this);
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serialize\Groups({"api:purchaseorders", "product:edit"})
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $externalId;       

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=false)
     * @Serialize\Groups({"api:purchaseorders", "product:edit"})
     * @
     */
    private $name;

    /**
     * @var int Standard sales price, in eurocents
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $price;

    /**
     * @var Attribute
     *
     * @ORM\ManyToOne(targetEntity="Attribute", inversedBy="options")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", nullable=false)
     */
    private $attribute;

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
     * Set external id like from webshop
     *
     * @param int $name
     *
     * @return AttributeOption
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
     * @return AttributeOption
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
     * Get attribute
     *
     * @return Attribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set standard sales price, in euros (float)
     *
     * @param float $price
     *
     * @return AttributeOption
     */
    public function setPrice($price)
    {
        $this->price = $price * 100;

        return $this;
    }

    /**
     * Get standard sales price, in euros (float)
     *
     * @return float
     */
    public function getPrice()
    {
        return floatval($this->price) / 100;
    }
}
