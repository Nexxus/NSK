<?php

namespace TnTBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{   
    /**
     * @Route("/tnt", name="tnt")
     */
    public function TnTAction()
    {
        return $this->render('TnTBundle:Default:index.html.twig');
    }
}
