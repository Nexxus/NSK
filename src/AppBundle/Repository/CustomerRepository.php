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
        if ($user->hasRole("ROLE_PARTNER"))
            return $this->findBy(array("partner" => $user->getPartner() ?? -1), array('id' => 'DESC'));
        else
            return $this->findAll();
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

        if ($search->user->hasRole("ROLE_PARTNER"))
            $qb = $qb->andWhere('o.partner = :partner')->setParameter('partner', $search->user->getPartner() ?? -1); 
        elseif ($search->partner)
            $qb = $qb->andWhere("o.partner = :partner")->setParameter("partner", $search->partner);

        return $qb->getQuery()->getResult();
    }

    /**
     * This function also persists or detaches newCustomer object
     * 
     * @param Customer $newCustomer
     * @param string $origin
     * @return Customer
     */
    public function checkCustomerExists(Customer $newCustomer)
    {
        $zip = strtolower(str_replace(" ", "", $newCustomer->getZip() ?? $newCustomer->getZip2()));

        if ($zip) 
        {
            $qb = $this->getEntityManager()->createQueryBuilder()
                ->from("AppBundle:Customer", "c")->select("c")
                ->where("LOWER(REPLACE(c.zip, ' ', '')) = :zip")->setParameter("zip", $zip);
                
            if ($newCustomer->getName() && strlen($newCustomer->getName()) > 2) 
            {
                $result = $qb
                    ->andWhere("c.name = :name")->setParameter("name", $newCustomer->getName())
                    ->getQuery()->getResult();

                if (count($result) > 0)
                {
                    $newCustomer = null;
                    return $result[0];
                }                
            }

            if ($newCustomer->getEmail() && strlen($newCustomer->getEmail()) > 5) {
                $result = $qb
                    ->andWhere("c.email = :email")->setParameter("email", $newCustomer->getEmail())
                    ->getQuery()->getResult();

                if (count($result) > 0)
                {
                    $newCustomer = null;
                    return $result[0];
                }  
            }

            if ($newCustomer->getPhone() && strlen($newCustomer->getPhone()) > 5) {
                $result = $qb
                    ->andWhere("REPLACE(c.phone, '-', '') = :phone")->setParameter("phone", str_replace($newCustomer->getPhone(), "-", ""))
                    ->getQuery()->getResult();

                if (count($result) > 0)
                {
                    $newCustomer = null;
                    return $result[0];
                }
            }
            
            // loose comparision, strict result count
            if ($newCustomer->getName() && strlen($newCustomer->getName()) > 4) 
            {
                $result = $qb
                    ->andWhere("SOUNDEX(c.name) like SOUNDEX(:name)")->setParameter("name", $newCustomer->getName())
                    ->getQuery()->getResult();

                if (count($result) == 1)
                {
                    $newCustomer = null;
                    return $result[0];
                }                
            }
        }

        $this->_em->persist($newCustomer);
        return $newCustomer;
    }   
}
