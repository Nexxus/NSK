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
use AppBundle\Entity\Customer;
use AppBundle\Entity\User;

class CustomerRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAll()
    {
        return $this->findBy(array(), array('id' => 'DESC'));
    }

    public function findMine(User $user)
    {
        if ($user->hasRole("ROLE_LOCAL"))
            return $this->findBy(array("location" => $user->getLocation()), array('id' => 'DESC'));
        else
            return $this->findBy(array(), array('id' => 'DESC'));
    }

    /**
     * This function searches in fields: Id, Kvk, Email, Name
     */
    public function findBySearchQuery($query)
    {
        if (is_numeric($query))
        {
            $q = $this->getEntityManager()
                ->createQuery("SELECT c FROM AppBundle:Customer c WHERE c.id = ?1 OR c.kvkNr = ?1 OR c.name LIKE ?2 ORDER BY c.id DESC");
        }
        else
        {
            $q = $this->getEntityManager()
                ->createQuery("SELECT c FROM AppBundle:Customer c WHERE c.email = ?1 OR c.name LIKE ?2 ORDER BY c.id DESC");
        }

        $q = $q
            ->setParameter(1, $query)
            ->setParameter(2, '%' . $query . '%');

        return $q->getResult();
    }

    /**
     * @param Customer $newCustomer
     * @return Customer
     */
    public function checkExists(Customer $newCustomer)
    {
        $q = $this->getEntityManager()
            ->createQuery("SELECT c FROM AppBundle:Customer c WHERE SOUNDEX(c.name) like SOUNDEX(:name) or REPLACE(c.phone, '-', '') = :phone OR REPLACE(c.phone2, '-', '') = :phone OR c.email = :email")
            ->setParameter("name", $newCustomer->getName())
            ->setParameter("phone", $newCustomer->getPhone())
            ->setParameter("email", $newCustomer->getEmail());

        $result = $q->getResult();

        if (count($result) == 1)
        {
            return $result[0];
        }
        else
        {
            return $newCustomer;
        }
    }
}
