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

namespace AppBundle\Repository;
use AppBundle\Entity\AttributeOption;

class AttributeRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAll()
    {
        return $this->findBy(array(), array('id' => 'DESC'));
    }

    /**
     * This function searches in fields: Code, Name
     */
    public function findBySearchQuery($query)
    {
        $q = $this->getEntityManager()
                ->createQuery("SELECT a FROM AppBundle:Attribute a WHERE a.attr_code = ?1 OR a.name LIKE ?2 ORDER BY a.id DESC")
                ->setParameter(1, $query)
                ->setParameter(2, '%' . $query . '%');

        return $q->getResult();
    }

    public function findAttributeOptionsForApi()
    {
        $q = $this->getEntityManager()
                ->createQuery("SELECT ao FROM AppBundle:AttributeOption ao WHERE ao.attribute IN (SELECT a FROM AppBundle:Attribute a WHERE a.externalId IS NOT NULL AND a.isPublic = true)");

        return $q->getResult();
    }
}
