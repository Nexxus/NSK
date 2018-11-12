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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Pickup
 *
 * @ORM\Entity
 * @ORM\Table(name="pickup")
 */
class Pickup
{
    const DATADESTRUCTION_NONE = 0;
    const DATADESTRUCTION_FORMAT = 1;
    const DATADESTRUCTION_STATEMENT = 2;
    const DATADESTRUCTION_SHRED = 3;
    const DATADESTRUCTION_KILLDISK = 4;

    public function __construct() {
        $this->images = new ArrayCollection();
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
     * @ORM\OneToOne(targetEntity="PurchaseOrder", inversedBy="pickup")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $order;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $pickupDate;

    /**
     * @var int Use constants
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dataDestruction;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var ArrayCollection|PickupImageFile[]
     *
     * @ORM\OneToMany(targetEntity="PickupImageFile", mappedBy="pickup", fetch="EAGER")
     */
    private $images;

    /**
     * @var PickupAgreementFile
     *
     * @ORM\OneToOne(targetEntity="PickupAgreementFile", mappedBy="pickup", fetch="EAGER")
     * @ORM\JoinColumn(name="agreement_id", referencedColumnName="id")
     */
    private $agreement;

    /**
     * @return Pickup
     */
    public function setOrder(PurchaseOrder $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return PurchaseOrder
     */
    public function getOrder()
    {
        return $this->order;
    }

    public function getPickupDate()
    {
        return $this->pickupDate;
    }

    public function setPickupDate($pickupDate)
    {
        $this->pickupDate = $pickupDate;
        return $this;
    }

    /**
     * Set dataDestruction
     *
     * @param integer $dataDestruction
     */
    public function setDataDestruction($dataDestruction)
    {
        $this->dataDestruction = $dataDestruction;

        return $this;
    }

    /**
     * Get dataDestruction
     *
     * @return integer
     */
    public function getDataDestruction()
    {
        return $this->dataDestruction;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Pickup
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

    public function addImage(PickupImageFile $image)
    {
        $this->images[] = $image;

        return $this;
    }

    public function removeImage(PickupImageFile $image)
    {
        $this->images->removeElement($image);
    }

    public function getImages()
    {
        return $this->images;
    }

    public function setAgreement(PickupAgreementFile $agreement)
    {
        $this->agreement = $agreement;

        return $this;
    }

    public function getAgreement()
    {
        return $this->agreement;
    }
}
