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

use AppBundle\Entity\User;
use AppBundle\Form\UserForm;
use AppBundle\Form\UserNewForm;
Use Symfony\Component\Form\FormError;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;


/**
* @Route("/admin/user")
*/
class UserController extends Controller
{

    /**
     * @Route("/", name="admin_user_index")
     * @Method({"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository("AppBundle:User")->findAll();

        return $this->render('AppBundle:User:index.html.twig',
                ['users' => $users]);
    }

    /**
     * @Route("/edit/{id}", name="admin_user_edit")
     * @Method({"GET", "POST"})
     */
    public function editUserAction(Request $request, $id) {

        $em = $this->getDoctrine()->getManager();

        if ($id == 0)
        {
            $user = new User();
            $user->setEnabled(true);
        }
        else
        {
            $user = $em->getRepository('AppBundle:User')->find($id);
        }

        $form = $this->createForm(UserForm::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            if ($form->get('plainPassword')->getData())
            {
                $encoder = $this->container->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $user->getPassword());
                $user->setPassword($encoded);
            }

            $role = $form->get('role')->getData();
            $user->setRoles([$role]);

            $em->persist($user);

            try
            {
                $em->flush($user);
                return $this->redirectToRoute('admin_user_index');
            }
            catch (UniqueConstraintViolationException $e) {
                $form->get('username')->addError(new FormError('Username already exists'));
            }
        }

        return $this->render('AppBundle:User:edit.html.twig',
                ['form' => $form->createView()]);
    }

    /**
     * @Route("/delete/{id}", name="admin_user_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($em->getReference(User::class, $id));
        $em->flush();

        return $this->redirectToRoute('admin_user_index');
    }
}
