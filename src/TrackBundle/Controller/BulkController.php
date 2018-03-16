<?php

namespace TrackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/track/bulk")
 */
class BulkController extends ProductController
{
    /**
     * @Route("/edit", name="track_bulk_edit")
     * @Method("GET")
     */
    public function bulkEditAction()
    {
        // get all items
        
        // if all items are the same type, give attribute options
        
        return $this->render('TrackBundle:Bulk:edit.html.twig', array(
        ));
    }

}
