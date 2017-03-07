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
    public function TnTAction()
    {
        //$builder = $this->get('app.menubuilder');
        //$crumb = $builder->createBreadcrumb();
        
        return $this->render('TnTBundle:Default:index.html.twig');
    }
}
