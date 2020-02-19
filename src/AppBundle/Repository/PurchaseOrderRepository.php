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

use AppBundle\Entity\PurchaseOrder;
use AppBundle\Entity\User;
use AppBundle\Entity\Pickup;

class PurchaseOrderRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAll()
    {
        return $this->findBy(array(), array('id' => 'DESC'));
    }

    public function findById($id)
    {
        return $this->findOneBy(array("id" => $id));
    }

    public function findBySearchQuery(\AppBundle\Helper\IndexSearchContainer $search)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->from("AppBundle:PurchaseOrder", "o")->select("o")->orderBy("o.id", "DESC");

        if ($search->query)
            $qb = $qb->andWhere("o.orderNr = :query")->setParameter("query", $search->query);

        if ($search->status)
            $qb = $qb->andWhere("o.status = :status")->setParameter("status", $search->status);

        return $qb->getQuery()->getResult();
    }

    public function findPickupEvents(User $user, $baseUrl)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->from("AppBundle:Pickup", "p")
            ->join("AppBundle:PurchaseOrder", "o")
            ->where("p.realPickupDate BETWEEN :start AND :end")
            ->setParameter("start", new \DateTime("first day of last month"))
            ->setParameter("end", (new \DateTime())->modify('+1 year'))
            ->select("p");

        $pickups = $qb->getQuery()->getResult();

        $events = array();

        foreach ($pickups as $pickup) {
            /** @var Pickup $pickup */
            
            $title = $pickup->getLogistics() ? $pickup->getLogistics()->getUsername() : "Pickup";

            if ($pickup->getOrder() && $pickup->getOrder()->getProductRelations()->count() > 0)
                $title .= " - " . $pickup->getOrder()->getProductRelations()->first()->getProduct()->getName();

            $event = [
                'title' => $title,
                'id' => $pickup->getId(),
                'url' => $baseUrl . '/' . $pickup->getOrder()->getId(),
                'color' => $pickup->getOrder()->getStatus()->getColor(),
                'start' => $pickup->getRealPickupDate()->format(\DateTime::ATOM),
                'end' => $pickup->getRealPickupDate()->modify("+1 hour")->format(\DateTime::ATOM),
            ];

            $events[] = $event;
        }

        return $events;
    }

    public function findLastPurchases(User $user, $pickupsOnly = false) {

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->from("AppBundle:PurchaseOrder", "o")
            ->select("o")
            ->orderBy("o.orderDate", "DESC")
            ->setMaxResults(5);

        if ($pickupsOnly)
            $qb = $qb->join("o.pickup", "p");

        return $qb->getQuery()->getResult();
    }

    public function findPurchasesPerDay(User $user) {

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->from("AppBundle:PurchaseOrder", "o")
            ->select("YEAR(o.orderDate) as orderYear, MONTH(o.orderDate) as orderMonth, DAY(o.orderDate) as orderDay, COUNT(o) as quantity")
            ->groupBy("orderYear")->addGroupBy("orderMonth")->addGroupBy("orderDay")
            ->orderBy("orderYear")->addOrderBy("orderMonth")->addOrderBy("orderDay");

        return $qb->getQuery()->getResult();        
    }

    public function generateOrderNr(PurchaseOrder $order)
    {
        $orderNr = $order->getOrderDate()->format("Y") . sprintf('%06d', $order->getId());
        $order->setOrderNr($orderNr);
        return $orderNr;
    }
}
