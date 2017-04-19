<?php

namespace TrackBundle\Controller;

use TrackBundle\Entity\ProductStatus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;	
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


/**
 * @Route("/admin/status")
 */
class ProductStatusController extends Controller
{
    /**
     * @Route("/", name="status_index")
     */
    public function indexAction() 
    {
        $productstatus = $this->getDoctrine()
                ->getRepository('TrackBundle:ProductStatus')
                ->findAll();
        
        return $this->render('admin/status/index.html.twig', array(
            'productstatus' => $productstatus)); 
    }
    
    /**
     * @Route("/create", name="status_create")
     */
    public function createAction()
    {
        $status = new ProductStatus();
        $status->setName("Product Status Name");
        $status->setPindex(1);
        
        $form = $this->createFormBuilder($status)
                ->add('name', TextType::class)
                ->add('pindex', IntegerType::class)
                ->add('save', SubmitType::class, array('label' => 'Create Status'))
                ->getForm();
        
        return $this->render('admin/status/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    
    /**
     * @Route("/edit/{id}", name="status_edit")
     */
    public function editAction($id)
    {
        
    }
}
