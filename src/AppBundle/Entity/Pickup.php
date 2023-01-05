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
use JMS\Serializer\Annotation as Serialize;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Pickup
 *
 * @ORM\Entity
 * @ORM\Table(name="pickup",indexes={@ORM\Index(name="idx_realPickupDate", columns={"real_pickup_date"})})
 */
class Pickup
{
    const DATADESTRUCTION_NONE = 0;
    const DATADESTRUCTION_FORMAT = 1;
    const DATADESTRUCTION_STATEMENT = 2;
    const DATADESTRUCTION_SHRED = 3;
    const DATADESTRUCTION_KILLDISK = 4;

    public function __construct(PurchaseOrder $order) {
        $this->order = $order;
        $this->images = new ArrayCollection();
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
     * @ORM\OneToOne(targetEntity="PurchaseOrder", inversedBy="pickup")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     * @Serialize\Groups({"api:purchaseorders"})
     */
    private $order;

    /**
     * @var \DateTime As proposed by supplier in public form
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Serialize\Groups({"api:purchaseorders"})
     */
    private $pickupDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Serialize\Groups({"api:purchaseorders"})
     */
    private $realPickupDate;

    /**
     * @var User Driver that will pick up the order
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="logistics_id", referencedColumnName="id")
     * @Serialize\MaxDepth(depth=1)
     */
    private $logistics;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serialize\Groups({"api:purchaseorders"})
     */
    private $origin;

    /**
     * @var int Use constants
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Serialize\Groups({"api:purchaseorders"})
     */
    private $dataDestruction;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     * @Serialize\Groups({"api:purchaseorders"})
     */
    private $description;

    /**
     * @var ArrayCollection|PickupImageFile[]
     *
     * @ORM\OneToMany(targetEntity="PickupImageFile", mappedBy="pickup", cascade={"all"}, orphanRemoval=true)
     * @Serialize\Groups({"api:purchaseorders"})
     */
    private $images;

    /**
     * @var PickupAgreementFile
     *
     * @ORM\OneToOne(targetEntity="PickupAgreementFile", mappedBy="pickup", cascade={"all"}, orphanRemoval=true)
     * @Serialize\Groups({"api:purchaseorders"})
     */
    private $agreement;

    public function getId()
    {
        return $this->id;
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

    public function setRealPickupDate($realPickupDate)
    {
        $this->realPickupDate = $realPickupDate;
        return $this;
    }

    public function getRealPickupDate()
    {
        return $this->realPickupDate;
    }

    public function setPickupDate($pickupDate)
    {
        $this->pickupDate = $pickupDate;
        return $this;
    }

    /**
     * Set dataDestruction
     *
     * @param int $dataDestruction
     */
    public function setDataDestruction($dataDestruction)
    {
        $this->dataDestruction = $dataDestruction;

        return $this;
    }

    /**
     * Get dataDestruction
     *
     * @return int
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

    public function setOrigin($origin)
    {
        $this->origin = $origin;

        return $this;
    }

    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param User $logistics
     */
    public function setLogistics($logistics)
    {
        $this->logistics = $logistics;

        return $this;
    }

    public function getLogistics()
    {
        return $this->logistics;
    }
}
