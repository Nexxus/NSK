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

use AppBundle\Entity\Task;
use AppBundle\Entity\ProductType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Form\IndexSearchForm;

/**
 * Task controller.
 *
 * @Route("admin/task")
 */
class TaskController extends Controller
{
    /**
     * Lists all task entities.
     *
     * @Route("/", name="admin_task_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Task');

        $tasks = array();

        $form = $this->createForm(IndexSearchForm::class, array());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $data = $form->getData();
            $tasks = $repo->findBySearchQuery($data['query']);
        }
        else
        {
            $tasks = $repo->findAll();
        }

        $paginator = $this->get('knp_paginator');
        $tasksPage = $paginator->paginate($tasks, $request->query->getInt('page', 1), 10);

        return $this->render('AppBundle:Task:index.html.twig', array(
            'tasks' => $tasksPage,
            'form' => $form->createView()
            ));
    }

    /**
     * Displays a form to edit an task entity.
     *
     * @Route("/edit/{id}", name="admin_task_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if ($id == 0)
        {
            $task = new Task();
        }
        else
        {
            $task = $em->getRepository('AppBundle:Task')->find($id);
        }

        $form = $this->createFormBuilder($task)
                ->add('name')
                ->add('description', TextareaType::class, array('required' => false))
                ->add('productTypes', EntityType::class, array(
                    'label' => 'Products',
                    'required' => false,
                    'multiple' => true,
                    'expanded' => false,
                    'class' => ProductType::class,
                    'choice_label' => 'name',
                    'attr' => ['class' => 'multiselect']))
                ->add('save', SubmitType::class, array('attr' => ['class' => 'btn-success btn-120']))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($task);
            $em->flush();

            return $this->redirectToRoute('admin_task_index');
        }

        return $this->render('AppBundle:Task:edit.html.twig', array(
            'task' => $task,
            'form' => $form->createView()
        ));
    }

    /**
     * Deletes a task entity.
     *
     * @Route("/delete/{id}", name="admin_task_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($em->getReference(Task::class, $id));
        $em->flush();

        return $this->redirectToRoute('admin_task_index');
    }
}
