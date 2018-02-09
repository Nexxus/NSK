<?php

namespace TrackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("track/checklist")
 */
class ProductTaskController extends Controller
{
    
    /**
     * @Route("show/{id}", name="track_checklist_show")
     */
    public function showListAction($id) {
        return $this->render("product/checklist/list.html.twig");
    }
}
