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
        if ($user->hasRole("ROLE_LOCAL") || $user->hasRole("ROLE_LOGISTICS"))
            return $this->findBy(array("location" => $user->getLocationIds()), array('id' => 'DESC'));
        else
            return $this->findBy(array(), array('id' => 'DESC'));
    }

    /**
     * This function searches in fields: Id, Kvk, Email, Name
     */
    public function findBySearchQuery(\AppBundle\Helper\IndexSearchContainer $search)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->from("AppBundle:Customer", "o")->select("o")->orderBy("o.id", "DESC");

        if ($search->query)
        {
            if (is_numeric($search->query))
            {
                $qb = $qb->andWhere("o.id = :query OR o.kvkNr = :query OR o.name LIKE :queryLike");
            }
            else
            {
                $qb = $qb->andWhere("o.email = :query OR o.name LIKE :queryLike");
            }

            $qb = $qb->setParameter("query", $search->query)->setParameter("queryLike", '%'.$search->query.'%');
        }

        if ($search->location)
            $qb = $qb->andWhere("o.location = :location")->setParameter("location", $search->location);
        elseif ($search->user->hasRole("ROLE_LOCAL") || $search->user->hasRole("ROLE_LOGISTICS"))
            $qb = $qb->andWhere('IDENTITY(o.location) IN (:locationIds)')->setParameter('locationIds', $search->user->getLocationIds()); 

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Customer $newCustomer
     * @return Customer
     */
    public function checkExists(Customer $newCustomer)
    {
        // First: strict comparision, loose result count

        $q = $this->getEntityManager()
            ->createQuery("SELECT c FROM AppBundle:Customer c WHERE c.name = :name OR c.email = :email")
            ->setParameter("name", $newCustomer->getName())
            ->setParameter("email", $newCustomer->getEmail());

        $result = $q->getResult();

        if (count($result) > 0)
        {
            $this->_em->detach($newCustomer);
            $newCustomer = null;
            return $result[0];
        }
        else
        {
            // Second: loose comparision, strict result count

            $q = $this->getEntityManager()
                ->createQuery("SELECT c FROM AppBundle:Customer c WHERE SOUNDEX(c.name) like SOUNDEX(:name) or REPLACE(c.phone, '-', '') = :phone OR REPLACE(c.phone2, '-', '') = :phone OR c.email = :email")
                ->setParameter("name", $newCustomer->getName())
                ->setParameter("phone", str_replace($newCustomer->getPhone(), "-", ""))
                ->setParameter("email", $newCustomer->getEmail());

            $result = $q->getResult();

            if (count($result) == 1)
            {
                $this->_em->detach($newCustomer);
                $newCustomer = null;
                return $result[0];
            }
            else
            {
                $this->_em->persist($newCustomer);
                return $newCustomer;
            }
        }
    }
}
