<?php

namespace TnTBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Appbundle\Builder;

class DefaultController extends Controller
{   
    /**
     * @Route("/tnt", name="tnt")
     */
    public function workloadAction()
    {
        $bread = array('Start', 'Track & Trace');
        
        return $this->render('TnTBundle/Default/workload.html.twig',array('bread' =>$bread));
    }
    
    /**
     * @Route("/tnt/inventaris", name="tnt_inventaris")
     */
    public function inventarisAction()
    {
        $bread = array('Start', 'Track & Trace');
        
        return $this->render('TnTBundle/Default/workload.html.twig',array('bread' =>$bread));
    }
    
    /**
     * @Route("/tnt/verkopen", name="tnt_verkopen")
     */
    public function verkopenAction()
    {
        $bread = array('Start', 'Track & Trace');
        
        return $this->render('TnTBundle/Default/workload.html.twig',array('bread' =>$bread));
    }
}
