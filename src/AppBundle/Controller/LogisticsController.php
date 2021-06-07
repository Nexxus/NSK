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

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
* @Route("/logistics")
*/
class LogisticsController extends Controller
{
    /**
     * @Route("/", name="logistics_calendar")
     */
    public function calendarAction()
    {
        $em = $this->getDoctrine()->getManager();

        $events1 = $em->getRepository('AppBundle:PurchaseOrder')->findPickupEvents($this->generateUrl("purchaseorder_edit", ['id' => 0]));
        $events2 = $em->getRepository('AppBundle:SalesOrder')->findDeliveryEvents($this->generateUrl("salesorder_edit", ['id' => 0]));
        
        return $this->render('AppBundle:Logistics:calendar.html.twig', array(
            'events' => array_merge($events1, $events2),
        ));
    }
}
