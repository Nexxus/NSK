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

/** @ORM\Entity */
class PickupImageFile extends AFile
{
    public function __construct(Pickup $pickup, $originalClientFilename, $uniqueServerFilename) {
        $this->pickup = $pickup;
        $pickup->addImage($this);
        parent::__construct($originalClientFilename, $uniqueServerFilename);
    }

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pickup", inversedBy="images")
     * @ORM\JoinColumn(name="pickup_id", referencedColumnName="id")
     */
    private $pickup;

    public function getPickup()
    {
        return $this->pickup;
    }
}

