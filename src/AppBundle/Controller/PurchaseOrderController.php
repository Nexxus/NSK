<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Supplier;
use AppBundle\Entity\PurchaseOrder;
use AppBundle\Form\IndexSearchForm;
use AppBundle\Form\IndexBulkEditForm;
use AppBundle\Form\PurchaseOrderForm;
use AppBundle\Form\PurchaseOrderBulkEditForm;
use Symfony\Component\Form\FormError;

/**
 * @Route("/purchaseorder")
 */
class PurchaseOrderController extends Controller
{
    use PdfControllerTrait;

    /**
     * @Route("/", name="purchaseorder_index")
     */
    public function indexAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository(PurchaseOrder::class);

        $orders = array();

        $container = new \AppBundle\Helper\IndexSearchContainer($this->getUser(), PurchaseOrder::class);

        $form = $this->createForm(IndexSearchForm::class, $container);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $container->isSearchable())
        {
            $orders = $repo->findBySearchQuery($container);
            $pageLength = 200;
        }
        else
        {
            $orders = $repo->findMine($this->getUser());
            $pageLength = 20;
        }

        $paginator = $this->get('knp_paginator');
        $ordersPage = $paginator->paginate($orders, $request->query->getInt('page', 1), $pageLength);

        return $this->render('AppBundle:PurchaseOrder:index.html.twig', array(
            'orders' => $ordersPage,
            'form' => $form->createView(),
            'formBulkEdit' => $this->createForm(IndexBulkEditForm::class, $orders)->createView()
            ));
    }

    /**
     * @Route("/bulkedit/{success}", name="purchaseorder_bulkedit")
     */
    public function bulkEditAction(Request $request, $success = null)
    {
        $em = $this->getDoctrine()->getManager();

        // Get variables from IndexBulkEditForm
        $action = $request->query->get('index_bulk_edit_form')['action'];
        $orderIds = $request->query->get('index_bulk_edit_form')['index'];
        $orders = $em->getRepository(PurchaseOrder::class)->findBy(['id' => $orderIds]);

        if ($action == "status")
        {
            $form = $this->createForm(PurchaseOrderBulkEditForm::class, $orders, array('user' => $this->getUser()));

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid())
            {
                $status = $form->get("status")->getData();
                
                foreach ($orders as $order)
                {
                    if ($status)
                        $order->setStatus($status);
                }
                
                $em->flush();

                return $this->redirectToRoute("purchaseorder_bulkedit", array('index_bulk_edit_form[action]' => $action, 'index_bulk_edit_form[index]' => $orderIds, 'success' => true));
            }
            else if ($form->isSubmitted())
            {
                $success = false;
            }

            return $this->render('AppBundle:PurchaseOrder:bulkedit.html.twig', array(
                'form' => $form->createView(),
                'success' => $success
            ));
        }
        else {
            return $this->bulkPrint($action, $orders);
        }
    }

    /**
     * @param string $object
     * @param PurchaseOrder[] $orders
     */
    private function bulkPrint($object, $orders) {

        $html = array();
        $mPdfConfiguration = ['', 'A4' ,'','',10,10,10,10,0,0,'P'];

        if (!count($orders)) {
            $html = "No (valid) orders to print";
        }
        else
        {
            foreach ($orders as $order)
            {
                /** @var PurchaseOrder $order */

                switch ($object) {
                    case "orders":
                        $html[] = $this->render('AppBundle:PurchaseOrder:print.html.twig', array('order' => $order));
                        break;                
                }
            }
        }

        //return new Response($html[0]);

        return $this->getPdfResponse("Bulk print", $html, $mPdfConfiguration);
    }    

    /**
     * @Route("/edit/{id}/{success}", name="purchaseorder_edit")
     */
    public function editAction(Request $request, $id = 0, $success = null)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var \AppBundle\Repository\PurchaseOrderRepository */
        $repo = $em->getRepository('AppBundle:PurchaseOrder');

        if ($id == 0)
        {
            $order = new PurchaseOrder();
            $order->setOrderDate(new \DateTime());
        }
        else
        {
            /** @var PurchaseOrder */
            $order = $repo->find($id);
        }

        $form = $this->createForm(PurchaseOrderForm::class, $order, array('user' => $this->getUser()));

        $form->handleRequest($request);

        if ($form->isSubmitted())
        {
            if ($form->get('newOrExistingSupplier')->getData() == 'existing')
            {
                if (!$order->getSupplier())
                {
                    $form->get('supplier')->addError(new FormError('Please select existing supplier'));
                }
            }
            else
            {
                /** @var Supplier */
                $newSupplier = $form->get('newSupplier')->getData();
                $newSupplier->addPurchaseOrder($order);
                $em->persist($newSupplier);
                $order->setSupplier($newSupplier);
            }

            if ($form->isValid())
            {                
                $statusChanged = $this->isChanged($order, 'status');

                $em->persist($order);

                $pickup = $order->getPickup();

                if ($pickup) {
                    $statusChanged = $statusChanged ? true : $this->isChanged($pickup, 'pickupDate');
                    $em->persist($pickup);
                    $pickup->setRealPickupDate($form->get("pickupDate")->getData());
                    $pickup->setLogistics($form->get("logistics")->getData());
                }

                try {
                    $em->flush();
                }
                catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                    $form->get('orderNr')->addError(new FormError('Already exists'));
                    $success = false;
                }

                if ($success !== false)
                {
                    if (!$order->getOrderNr())
                    {
                        $order->setOrderNr($repo->generateOrderNr($order));
                        $em->flush();
                    }

                    if ($statusChanged)
                        $this->sendChangedMail($order);

                    return $this->redirectToRoute("purchaseorder_edit", array('id' => $order->getId(), 'success' => true));
                }
            }
            else
            {
                $success = false;
            }
        }

        return $this->render('AppBundle:PurchaseOrder:edit.html.twig', array(
                'order' => $order,
                'form' => $form->createView(),
                'success' => $success,
            ));

    }

    private function isChanged($object, $propertyName)
    {
        $em = $this->getDoctrine()->getManager();
        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets();
        $changeSet = $uow->getEntityChangeSet($object);
        return isset($changeSet[$propertyName]);
    }

    private function sendChangedMail(PurchaseOrder $order)
    {
        $body = $order->getStatus()->getMailbody();
        $to = $order->getSupplier()->getEmail();
        //$to = "ronald.debakker@copiatek.nl";

        if (!$to || !$body)
            return;       

        $body = str_replace("%supplier.name%", $order->getSupplier()->getName() ?? "leverancier", $body);
        $body = str_replace("%user.name%", $this->getUser()->getFullname(), $body);
        $body = str_replace("%order.nr%", $order->getOrderNr(), $body);

        if ($order->getPickup() && $order->getPickup()->getRealPickupDate())
        {
            $body = str_replace("%pickup.datetime%", $order->getPickup()->getRealPickupDate()->format("j-n-Y G:i"), $body);
            $body = str_replace("%pickup.date%", $order->getPickup()->getRealPickupDate()->format("j-n-Y"), $body);
        }        
        elseif ($order->getPickup() && $order->getPickup()->getPickupDate())
        {
            $body = str_replace("%pickup.datetime%", $order->getPickup()->getPickupDate()->format("j-n-Y G:i"), $body);
            $body = str_replace("%pickup.date%", $order->getPickup()->getPickupDate()->format("j-n-Y"), $body);
        }

        $message = (new \Swift_Message('Status of datum van uw opdracht is gewijzigd'))
            ->setFrom('logistiek@copiatek.nl')
            ->setTo($to)
            ->setBody($body, 'text/plain');

        /** @var \Swift_Mailer */
        $mailer = $this->get('mailer');
        $mailer->send($message);
    }    

    /**
     * @Route("/delete/{id}", name="purchaseorder_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var PurchaseOrder */
        $order = $em->getRepository('AppBundle:PurchaseOrder')->find($id);

        $em->remove($order);
        $em->flush();

        return $this->redirectToRoute('purchaseorder_index');
    }

    /**
     * @Route("/print/{id}", name="purchaseorder_print")
     */
    public function printAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository('AppBundle:PurchaseOrder')->find($id);

        $supplierBarcode = "";
        if ($order->getSupplier() && strlen($order->getSupplier()->getName()) > 20)
            $supplierBarcode = substr($this->cleanString($order->getSupplier()->getName(), 0, 20));
        elseif ($order->getSupplier())
            $supplierBarcode = $this->cleanString($order->getSupplier()->getName());

        $html = $this->render('AppBundle:PurchaseOrder:print.html.twig', array('order' => $order, 'supplierBarcode' => $supplierBarcode));
        $mPdfConfiguration = ['', 'A4' ,'','',10,10,10,10,0,0,'P'];

        return $this->getPdfResponse("Nexxus purchase order", $html, $mPdfConfiguration);
    }

    private function cleanString($text) {
        $utf8 = array(
            '/[áàâãªä]/u'   =>   'a',
            '/[ÁÀÂÃÄ]/u'    =>   'A',
            '/[ÍÌÎÏ]/u'     =>   'I',
            '/[íìîï]/u'     =>   'i',
            '/[éèêë]/u'     =>   'e',
            '/[ÉÈÊË]/u'     =>   'E',
            '/[óòôõºö]/u'   =>   'o',
            '/[ÓÒÔÕÖ]/u'    =>   'O',
            '/[úùûü]/u'     =>   'u',
            '/[ÚÙÛÜ]/u'     =>   'U',
            '/ç/'           =>   'c',
            '/Ç/'           =>   'C',
            '/ñ/'           =>   'n',
            '/Ñ/'           =>   'N',
            '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
            '/[“”«»„]/u'    =>   ' ', // Double quote
            '/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
            '/&/'           =>   'en', 
        );
        return preg_replace(array_keys($utf8), array_values($utf8), $text);
    }
}
