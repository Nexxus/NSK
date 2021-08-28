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

use AppBundle\Entity\SalesOrder;
use AppBundle\Entity\User;

class SalesOrderRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAll()
    {
        return $this->findBy(array(), array('id' => 'DESC'));
    }

    public function findMine(User $user)
    {
        if ($user->hasRole("ROLE_PARTNER"))
        {
            return $this->getEntityManager()->createQueryBuilder()
                ->from("AppBundle:SalesOrder", "o")->join("o.customer", "c")->select("o")->orderBy("o.id", "DESC")
                ->where("c.partner = :partner")->setParameter("partner", $user->getPartner() ?? -1)
                ->getQuery()->getResult();
        }
        else
            return $this->findAll();
    }

    public function findBySearchQuery(\AppBundle\Helper\IndexSearchContainer $search)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->from("AppBundle:SalesOrder", "o")->join("o.customer", "c")->select("o")->orderBy("o.id", "DESC");

        if ($search->query)
            $qb = $qb->andWhere("o.orderNr = :query")->setParameter("query", $search->query);

        if ($search->status)
            $qb = $qb->andWhere("o.status = :status")->setParameter("status", $search->status);

        if ($search->user->hasRole("ROLE_PARTNER"))
            $qb = $qb->andWhere('c.partner = :partner')->setParameter('partner', $search->user->getPartner() ?? -1); 
        elseif ($search->partner)
            $qb = $qb->andWhere("c.partner = :partner")->setParameter("partner", $search->partner);

        return $qb->getQuery()->getResult();
    }

    public function generateSalesOrderNr(SalesOrder $order)
    {
        $orderNr = $order->getOrderDate()->format("Y") . sprintf('%06d', $order->getId());
        $order->setOrderNr($orderNr);
        return $orderNr;
    }

    public function findLastSales(User $user, $repairsOnly = false) {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->from("AppBundle:SalesOrder", "o")
            ->select("o")
            ->orderBy("o.orderDate", "DESC")
            ->setMaxResults(5);

        if ($repairsOnly)
            $qb = $qb->join("o.repair", "r");

        return $qb->getQuery()->getResult();
    }

    public function findSalesPerDay(User $user) {

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->from("AppBundle:SalesOrder", "o")
            ->join("o.productRelations", "r")
            ->select("YEAR(o.orderDate) as orderYear, MONTH(o.orderDate) as orderMonth, DAY(o.orderDate) as orderDay, SUM(r.quantity * r.price) as revenue")
            ->groupBy("orderYear")->addGroupBy("orderMonth")->addGroupBy("orderDay")
            ->orderBy("orderYear")->addOrderBy("orderMonth")->addOrderBy("orderDay");

        return $qb->getQuery()->getResult();        
    }  
    
    public function findRepairsPerDay(User $user) {

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->from("AppBundle:SalesOrder", "o")
            ->join("o.repair", "r")
            ->select("YEAR(o.orderDate) as orderYear, MONTH(o.orderDate) as orderMonth, DAY(o.orderDate) as orderDay, COUNT(o) as quantity")
            ->groupBy("orderYear")->addGroupBy("orderMonth")->addGroupBy("orderDay")
            ->orderBy("orderYear")->addOrderBy("orderMonth")->addOrderBy("orderDay");

        return $qb->getQuery()->getResult();        
    }

    public function findDeliveryEvents($baseUrl)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->from("AppBundle:SalesOrder", "o")
            ->where("o.deliveryDate BETWEEN :start AND :end")
            ->setParameter("start", new \DateTime("-1 year"))
            ->setParameter("end", (new \DateTime())->modify('+1 year'))
            ->select("o");

        $salesOrders = $qb->getQuery()->getResult();

        $events = array();

        foreach ($salesOrders as $salesOrder) {
            /** @var SalesOrder $salesOrder */
            
            $title = "Delivery of " . $salesOrder->getOrderNr();

            $event = [
                'title' => $title,
                'id' => $salesOrder->getId(),
                'url' => $baseUrl . '/' . $salesOrder->getId(),
                'color' => $salesOrder->getStatus()->getColor(),
                'start' => $salesOrder->getDeliveryDate()->format(\DateTime::ATOM),
                'end' =>   $salesOrder->getDeliveryDate()->modify("+1 hour")->format(\DateTime::ATOM),
            ];

            $events[] = $event;
        }

        return $events;
    }
}
