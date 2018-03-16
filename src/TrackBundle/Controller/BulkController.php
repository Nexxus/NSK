<?php

namespace TrackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


/**
 * @Route("/track/bulk")
 */
class BulkController extends Controller
{
    /**
     * @Route("/edit", name="track_bulk_edit")
     */
    public function editAction()
    {
        return $this->render('TrackBundle:Bulk:edit.html.twig', array(
            // ...
        ));
    }

}
