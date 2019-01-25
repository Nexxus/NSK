<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Attribute;
use AppBundle\Entity\AttributeOption;
use AppBundle\Form\AttributeForm;
use AppBundle\Form\IndexSearchForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Attribute controller.
 *
 * @Route("admin/attribute")
 */
class AttributeController extends Controller
{
    /**
     * @Route("/", name="admin_attribute_index")
     */
    public function indexAction(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository('AppBundle:Attribute');

        $attributes = array();

        $container = new \AppBundle\Helper\IndexSearchContainer();
        $container->className = Attribute::class;

        $form = $this->createForm(IndexSearchForm::class, $container);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $attributes = $repo->findBySearchQuery($container->query);
        }
        else
        {
            $attributes = $repo->findAll();
        }

        $paginator = $this->get('knp_paginator');
        $attributesPage = $paginator->paginate($attributes, $request->query->getInt('page', 1), 20);

        return $this->render('AppBundle:Attribute:index.html.twig', array(
            'attributes' => $attributesPage,
            'form' => $form->createView()
            ));
    }

    /**
     * Displays a form to edit an existing attribute entity.
     *
     * @Route("/edit/{id}", name="admin_attribute_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if ($id == 0)
        {
            $attribute = new Attribute();
        }
        else
        {
            /** @var Attribute */
            $attribute = $em->getRepository('AppBundle:Attribute')->find($id);
        }

        $form = $this->createForm(AttributeForm::class, $attribute);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            if ($form->has('newOption') && $newOptionName = $form->get('newOption')->getData())
            {
                $newOption = new AttributeOption($attribute);
                $newOption->setName($newOptionName);
                $em->persist($newOption);
            }

            $em->persist($attribute);

            $em->flush();

            return $this->redirectToRoute('admin_attribute_index');
        }

        return $this->render('AppBundle:Attribute:edit.html.twig', array(
            'attribute' => $attribute,
            'form' => $form->createView()
        ));
    }

    /**
     * Deletes a attribute entity.
     *
     * @Route("delete/{id}", name="admin_attribute_delete")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($em->getReference(Attribute::class, $id));
        $em->flush();

        return $this->redirectToRoute('admin_attribute_index');
    }
}
