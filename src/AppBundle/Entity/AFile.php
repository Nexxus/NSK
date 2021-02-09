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
 * Copiatek � info@copiatek.nl � Postbus 547 2501 CM Den Haag
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="afile")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"pr" = "ProductAttributeFile", "pi" = "PickupImageFile", "pa" = "PickupAgreementFile", "di" = "DeliveryImageFile" })
 */
abstract class AFile
{
    /**
     * @param string $originalClientFilename
     * @param string $uniqueServerFilename
     */
    public function __construct($originalClientFilename, $uniqueServerFilename) {
        $this->originalClientFilename = $originalClientFilename;
        $this->uniqueServerFilename = $uniqueServerFilename;
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $originalClientFilename;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $externalId;   

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
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $uniqueServerFilename;

    public function getOriginalClientFilename()
    {
        return $this->originalClientFilename;
    }

    public function getUniqueServerFilename()
    {
        return $this->uniqueServerFilename;
    }

    /**
     * Set external id like from webshop
     *
     * @param integer $name
     *
     * @return AFile
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
}

