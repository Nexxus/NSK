<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Attribute;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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

        $form = $this->createFormBuilder(array(), array('allow_extra_fields' => true))
            ->add('query', TextType::class, ['label' => false, 'attr' => ['placeholder' => 'Zoeken op Id, KvK, e-mail of (deel van) naam']])
            ->add('submit', SubmitType::class, ['label' => 'Search'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $data = $form->getData();
            $attributes = $repo->findBySearchQuery($data['query']);
        }
        else
        {
            $attributes = $repo->findAll();
        }

        $paginator = $this->get('knp_paginator');
        $attributesPage = $paginator->paginate($attributes, $request->query->getInt('page', 1), 10);

        return $this->render('AppBundle:Attribute:index.html.twig', array(
            'attributes' => $attributesPage,
            'form' => $form->createView()
            ));
    }

    /**
     * Creates a new attribute entity.
     *
     * @Route("/new", name="admin_attribute_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $attribute = new Attribute();
        $editForm = $this->createFormBuilder($attribute)
                ->add('name')
                ->add('attr_code')
                ->add('type', ChoiceType::class, [
                    'choices' => [
                        'Text' => $attribute::TYPE_TEXT,
                        'Selectbox' => $attribute::TYPE_SELECT,
                        'File' => $attribute::TYPE_FILE,
                        'Product' => $attribute::TYPE_PRODUCT
                    ]
                ])
                ->getForm();

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($attribute);
            $em->flush();

            return $this->redirectToRoute('admin_attribute_index');
        }

        return $this->render('AppBundle:Attribute:new.html.twig', array(
            'attribute' => $attribute,
            'form' => $editForm->createView(),
        ));
    }

    /**
     * Finds and displays a attribute entity.
     *
     * @Route("/{id}", name="admin_attribute_show")
     * @Method("GET")
     */
    public function showAction(Attribute $attribute)
    {
        $deleteForm = $this->createDeleteForm($attribute);

        return $this->render('AppBundle:Attribute:show.html.twig', array(
            'attribute' => $attribute,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing attribute entity.
     *
     * @Route("/{id}/edit", name="admin_attribute_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Attribute $attribute)
    {
        $deleteForm = $this->createDeleteForm($attribute);
        $editForm = $this->createForm('AppBundle\Form\AttributeForm', $attribute);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_attribute_edit', array('id' => $attribute->getId()));
        }

        return $this->render('AppBundle:Attribute:edit.html.twig', array(
            'attribute' => $attribute,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a attribute entity.
     *
     * @Route("/{id}", name="admin_attribute_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Attribute $attribute)
    {
        $form = $this->createDeleteForm($attribute);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($attribute);
            $em->flush();
        }

        return $this->redirectToRoute('admin_attribute_index');
    }

    /**
     * Creates a form to delete a attribute entity.
     *
     * @param Attribute $attribute The attribute entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Attribute $attribute)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_attribute_delete', array('id' => $attribute->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
