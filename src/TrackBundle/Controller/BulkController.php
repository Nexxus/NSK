<?php

namespace TrackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class BulkController extends Controller
{
    /**
     * @Route("/edit")
     */
    public function editAction()
    {
        return $this->render('TrackBundle:Bulk:edit.html.twig', array(
            // ...
        ));
    }

}
