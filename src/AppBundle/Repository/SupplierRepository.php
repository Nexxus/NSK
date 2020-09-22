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

use AppBundle\Entity\Supplier;
use AppBundle\Entity\User;

/**
 * SupplierRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SupplierRepository extends \Doctrine\ORM\EntityRepository
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
            ->from("AppBundle:Supplier", "o")->select("o")->orderBy("o.id", "DESC");

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
     * This function also persists or detaches newSupplier object
     *  
     * @param Supplier $newSupplier
     * @return Supplier
     */
    public function checkExists(Supplier $newSupplier)
    {
        $zip = strtolower(str_replace(" ", "", $newSupplier->getZip() ?? $newSupplier->getZip2()));

        if ($zip)
        {
            $qb = $this->getEntityManager()->createQueryBuilder()
                ->from("AppBundle:Supplier", "s")->select("s")
                ->where("LOWER(REPLACE(s.zip, ' ', '')) = :zip")->setParameter("zip", $zip);
                
            if ($newSupplier->getName() && strlen($newSupplier->getName()) > 2) 
            {
                $result = $qb
                    ->andWhere("s.name = :name")->setParameter("name", $newSupplier->getName())
                    ->getQuery()->getResult();

                if (count($result) > 0)
                {
                    $this->_em->detach($newSupplier);
                    $newSupplier = null;
                    return $result[0];
                }                
            }

            if ($newSupplier->getEmail() && strlen($newSupplier->getEmail()) > 5) {
                $result = $qb
                    ->andWhere("s.email = :email")->setParameter("email", $newSupplier->getEmail())
                    ->getQuery()->getResult();

                if (count($result) > 0)
                {
                    $this->_em->detach($newSupplier);
                    $newSupplier = null;
                    return $result[0];
                }               
            }

            if ($newSupplier->getPhone() && strlen($newSupplier->getPhone()) > 5) {
                $result = $qb
                    ->andWhere("REPLACE(s.phone, '-', '') = :phone")->setParameter("phone", str_replace($newSupplier->getPhone(), "-", ""))
                    ->getQuery()->getResult();

                if (count($result) > 0)
                {
                    $this->_em->detach($newSupplier);
                    $newSupplier = null;
                    return $result[0];
                }  
            }
            
            // loose comparision, strict result count
            if ($newSupplier->getName() && strlen($newSupplier->getName()) > 4) 
            {
                $result = $qb
                    ->andWhere("SOUNDEX(s.name) like SOUNDEX(:name)")->setParameter("name", $newSupplier->getName())
                    ->getQuery()->getResult();

                if (count($result) == 1)
                {
                    $this->_em->detach($newSupplier);
                    $newSupplier = null;
                    return $result[0];
                }                
            }
        }
        
        $this->_em->persist($newSupplier);
        return $newSupplier;
    }
}
