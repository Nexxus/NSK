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
        return $this->render('TnTBundle/Default/workload.html.twig');
    }
    
    /**
     * @Route("/tnt/inventaris", name="tnt_inventaris")
     */
    public function inventarisAction()
    {   
        return $this->render('TnTBundle/Default/inventaris.html.twig');
    }
    
    /**
     * @Route("/tnt/verkopen", name="tnt_verkopen")
     */
    public function verkopenAction()
    {
        return $this->render('TnTBundle/Default/verkopen.html.twig');
    }
}
