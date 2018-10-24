<?php

/*
 * Nexxus Stock Keeping (online voorraad beheer software)
 * Copyright (C) 2018 Copiatek Scan & Computer Solution BV
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see licenses.
 *
 * Copiatek – info@copiatek.nl – Postbus 547 2501 CM Den Haag
 */

namespace AppBundle\Controller;

use AppBundle\Entity\OrderStatus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("/admin/orderstatus")
 */
class OrderStatusController extends Controller
{
    /**
     * @Route("/", name="status_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
                  ' SELECT '
                . '     s.id,'
                . '     s.pindex,'
                . '     s.name,'
                . '     count(o.id) as order_count '
                . ' FROM AppBundle:OrderStatus s'
                . ' LEFT JOIN AppBundle:AOrder o'
                . '     WITH o.status = s.id'
                . ' WHERE '
                . '     s.pindex < 999'
                . ' GROUP BY '
                . '     s.id');
        $orderstatus = $query->getResult();

        return $this->render('AppBundle:OrderStatus:index.html.twig', array(
            'orderstatus' => $orderstatus));
    }

    /**
     * @Route("/create", name="status_create")
     */
    public function createAction(Request $request)
    {

        $status = new OrderStatus();
        $status->setName("Order Status Name");
        $status->setPindex(1);

        $em = $this->getDoctrine()->getRepository('AppBundle:OrderStatus');

        $statusall = $em->findAll();

        $form = $this->createFormBuilder($status);
        $form->add('name', TextType::class);

        // option to place before or after
        if(count($statusall) > 0 ) {
            $form->add('placement', ChoiceType::class, array(
                        'choices' => array(
                            'Before' => 'before',
                            'After' => 'after'
                        ),
                        'mapped' => false
                    ));
        }
        $form->add('isSale', CheckboxType::class, ['required' => false]);
        $form->add('isPurchase', CheckboxType::class, ['required' => false]);
        $form->add('save', SubmitType::class, array('label' => 'Create Status'));
        $form = $form->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted()) {
            $task = $form->getData();


            // get before or after field, does not actually exist in object
            if(count($statusall) > 0) {
                $pm = $form->get('placement')->getData();

                $pindex = $_POST['form']['pindex'];

                // make space for new entry
                if($pm=='after') {
                    $pindex = $pindex+1;
                }

                $status->setPindex($pindex);

                //echo "<pre>";print$em->_r($status);echo "</pre>";exit;

                $this->shiftIndex($pindex, "add");
            } else {
                $status->setPindex(1);
            }
            // save object
            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            return $this->redirectToRoute('status_index');
        }

        return $this->render('AppBundle:OrderStatus:new.html.twig', array(
            'form' => $form->createView(),
            'statusall' => $statusall,
        ));
    }


    /**
     * @Route("/edit/{id}", name="status_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $status = $em->getRepository('AppBundle:OrderStatus')
                    ->find($id);

        $form = $this->createFormBuilder($status)
                ->add('pindex')
                ->add('name')
                ->add('isSale', CheckboxType::class, ['required' => false])
                ->add('isPurchase', CheckboxType::class, ['required' => false])
                ->add('save', SubmitType::class,
                    array('label' => 'Edit Status')
                );

        $form = $form->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted()) {
            $em->persist($status);
            $em->flush();

            return $this->redirectToRoute('status_index');
        }


        return $this->render('AppBundle:OrderStatus:edit.html.twig', array(
                'form' => $form->createView(),
            ));
    }

    /**
     * @Route("/delete/{id}/{pindex}", name="status_delete")
     */
    public function deleteAction($id, $pindex)
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
                "DELETE FROM AppBundle:OrderStatus s"
                . " WHERE s.id = :statusid"
        )->setParameter("statusid", $id)
         ->getResult();

        $this->shiftIndex($pindex, "remove");

        return $this->redirectToRoute('status_index');
    }

    /**
     * Make space in the index for a new entry
     * Method add adds a space and remove places everything back
     *
     * @return int
     */
    private function shiftIndex($pindex, $method)
    {
        // !! unfinished function, not yet able to update multiple entries
        //https://stackoverflow.com/questions/4337751/doctrine-2-update-query-with-query-builder
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
                "SELECT s "
                . " FROM AppBundle:OrderStatus s"
                . " WHERE s.pindex >= :space"
                . " AND s.name != :name"
        )->setParameter('space', $pindex)
         ->setParameter('name', "Sold");
        $statuses = $query->getResult();

        if($method == "add") {
            foreach($statuses as $status) {
                $query = $em->createQuery(
                        "UPDATE AppBundle:OrderStatus s"
                        . " SET s.pindex = s.pindex+1"
                        . " WHERE s.id = :id"
                )->setParameter('id', $status->getId())
                ->getResult();
            }
        }
        if($method == "remove") {
            foreach($statuses as $status) {
                $query = $em->createQuery(
                        "UPDATE AppBundle:OrderStatus s"
                        . " SET s.pindex = s.pindex-1"
                        . " WHERE s.id = :id"
                )->setParameter('id', $status->getId())
                ->getResult();
            }
        }
        return 0;
    }
}
