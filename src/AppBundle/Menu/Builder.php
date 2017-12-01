<?php

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