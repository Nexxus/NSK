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
 * Copiatek - info@copiatek.nl - Postbus 547 2501 CM Den Haag
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as Serialize;

/**
 * Task
 *
 * @ORM\Table(name="task")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TaskRepository")
 */
class Task
{
    public function __construct() {
        $this->productTypes = new ArrayCollection();
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serialize\Groups({"product:index"})
     */
    private $id;

    /**
     * @var ArrayCollection Which product types can use this task
     *
     * @ORM\ManyToMany(targetEntity="ProductType", mappedBy="tasks")
     */
    private $productTypes;

        /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Serialize\Groups({"product:index"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

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
     * Set name
     *
     * @param string $name
     *
     * @return Task
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
     * @return Task
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
     * Add productType
     *
     * @param ProductType $productType
     *
     * @return Task
     */
    public function addProductType(ProductType $productType)
    {
        $this->productTypes[] = $productType;
        $productType->addTask($this);
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
        $productType->removeTask($this);
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
}
