<?php

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    
    public function createMainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        
        $menu->setChildrenAttribute('class', 'nav navbar-nav');
        
        /*$menu->addChild("<img height=24 src='images/logos/header-logoGlow.png' >",
          array(
            'uri' => '/',
            'extras' => array(
              'safe_label' => true
            )
          ));*/

        $menu->addChild('Home', array('route' => 'home'));
        $menu->addChild('Track & Trace', array('route' => 'track_index'));
        
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