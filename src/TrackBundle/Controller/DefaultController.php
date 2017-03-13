<?php

namespace TrackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{   
    /**
     * @Route("/track", name="track")
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
