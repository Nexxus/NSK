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
            $menu->addChild('Track & Trace', array('route' => 'track_index'));
        }

        // add admin menu items
        if($role->isGranted('ROLE_ADMIN')) {
            $menu->addChild('Admin', array('route' => 'admin_index'));
        }
        
        return $menu;
    }
    
    public function createtrackMenu(FactoryInterface $factory, array $options)
    {/*
        $menu = $factory->createItem('track');
        
        $menu->setChildrenAttribute('class', 'nav nav-tabs');
        
        $menu->addChild('Inventaris', array('route' => 'track_product_index'));
        $menu->addChild('Verkopen', array('uri' => '#'));
        
        return $menu;
     */
    }
}
