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

namespace AdminBundle\Controller;

use AppBundle\Entity\User;
use AdminBundle\Form\UserType;
use AdminBundle\Form\UserNewType;
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
    public function viewUserAction()
    {
        $em = $this->getDoctrine()->getManager();

        $userQuery = $em->getRepository("AppBundle:User")->findAll();

        return $this->render('AdminBundle:User:index.html.twig',
                ['users' => $userQuery]);
    }

    /**
     * @Route("/edit/{id}", name="admin_user_edit")
     * @Method({"GET", "POST"})
     */
    public function editUserAction(Request $request, User $user) {

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $role = $form->get('role')->getData();
            $user->setRoles([$role]);

            $em->persist($user);
            $em->flush($user);

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('AdminBundle:User:edit.html.twig',
                ['form' => $form->createView()]);
    }

    /**
     * @Route("/user/new", name="admin_user_new")
     * @Method({"GET", "POST"})
     */
    public function newUserAction(Request $request) {

        $user = new User();
        $user->setEnabled(true);

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(UserNewType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            try {
                $encoder = $this->container->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $user->getPassword());
                $user->setPassword($encoded);

                $em->persist($user);
                $em->flush($user);

                return $this->redirectToRoute('admin_user_index');
            }
            catch (UniqueConstraintViolationException $e) {
                $form->get('username')->addError(new FormError('Username already exists'));
            }
        }

        return $this->render('AdminBundle:User:new.html.twig',
                ['form' => $form->createView()]);
    }
}
