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

namespace AppBundle\Helper;

use AppBundle\Entity\User;

class IndexSearchContainer
{
    public function __construct(User $user, $className) {
        $this->user = $user;
        $this->className = $className;
    }
    
    /** @var string */
    public $className;

    /** @var string */
    public $query;

    /** @var \AppBundle\Entity\ProductStatus|\AppBundle\Entity\OrderStatus */
    public $status;

    /** @var string */
    public $type;

    /** @var \AppBundle\Entity\Location */
    public $location;

    /** @var User */
    public $user;

    /** @var \AppBundle\Entity\ProductType */
    public $producttype;

    /** @return bool */
    public function isSearchable() {
        if ($this->location || $this->status || $this->producttype || $this->query)
            return true;
        else
            return false;
    }
}