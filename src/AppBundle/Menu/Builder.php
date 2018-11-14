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

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function createMainMenu(FactoryInterface $factory, array $options)
    {
        $role = $this->container->get('security.authorization_checker');

        $menu = $factory->createItem('root');

        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        $menu->addChild('Home', array('route' => 'home'));

        // add user menu items
        if($role->isGranted('ROLE_USER')) {
            $menu->addChild('Voorraad', array('route' => 'product_index'));
            $menu->addChild('Inkoop', array('route' => 'purchaseorder_index'));
            $menu->addChild('Verkoop', array('route' => 'salesorder_index'));
            $menu->addChild('Klanten', array('route' => 'customer_index'));
            $menu->addChild('Leveranciers', array('route' => 'supplier_index'));
        }

        return $menu;
    }

    public function createUserMenu(FactoryInterface $factory, array $options)
    {
        $role = $this->container->get('security.authorization_checker');

        $menu = $factory->createItem('root');

        $menu->setChildrenAttribute('class', 'nav navbar-nav navbar-right');

        if($role->isGranted('ROLE_ADMIN')) {
            $menu->addChild('Admin', array('route' => 'admin_index'));
        }

        if($role->isGranted('ROLE_USER'))
        {
            $menu->addChild('Help', array('route' => 'underconstruction'));
            $menu->addChild('Logout', array('route' => 'fos_user_security_logout'));
        }
        else
        {
            $menu->addChild('Login', array('route' => 'fos_user_security_login'));
        }

        return $menu;
    }
}
