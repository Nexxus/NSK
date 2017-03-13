<?php

namespace TrackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Appbundle\Builder;

class DefaultController extends Controller
{   
    /**
     * @Route("/track", name="track")
     */
    public function workloadAction()
    {
        return $this->render('TrackBundle/Default/workload.html.twig');
    }
    
    /**
     * @Route("/track/inventaris", name="track_inventaris")
     */
    public function inventarisAction()
    {   
        return $this->render('TrackBundle/Default/inventaris.html.twig');
    }
    
    /**
     * @Route("/track/verkopen", name="track_verkopen")
     */
    public function verkopenAction()
    {
        return $this->render('TrackBundle/Default/verkopen.html.twig');
    }
}
