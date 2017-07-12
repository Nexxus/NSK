<?php

namespace TrackBundle\Controller;

use TrackBundle\Entity\ProductStatus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;	
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Doctrine\ORM\EntityManagerInterface;

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
    public function createAction(Request $request)
    {
        
        $status = new ProductStatus();
        $status->setName("Product Status Name");
        $status->setPindex(1);
        
        $em = $this->getDoctrine()->getRepository('TrackBundle:ProductStatus');
        
        $statusall = $em->findAll();
        
        $form = $this->createFormBuilder($status)
                ->add('name', TextType::class)
                ->add('placement', ChoiceType::class, array(
                    'choices' => array(
                        'Before' => 'before',
                        'After' => 'after'
                    ),
                    'mapped' => false
                ))
                ->add('save', SubmitType::class, array('label' => 'Create Status'))
                ->getForm();
        
        $form->handleRequest($request);
        
        if($form->isSubmitted()) {
            $task = $form->getData();
            
            // get before or after field, does not actually exist in object
            $pm = $form->get('placement')->getData();
            $pindex = $_POST['form']['pindex'];
            
            // make space for new entry
            if($pm=='before') {
                $pindex = $pindex-1;
            }
            
            $status->setPindex($pindex);
            
            echo "<pre>";print_r($status);echo "</pre>";exit;
            
            $this->shiftIndex($pindex);
            
            // save object
            $em->persist($task);
            $em->flush();
            
            return $this->redirectToRoute('status_index');
        }
        
        return $this->render('admin/status/new.html.twig', array(
            'form' => $form->createView(),
            'statusall' => $statusall,
        ));
    }
    
    
    /**
     * @Route("/edit/{id}", name="status_edit")
     */
    public function editAction($id)
    {
        
    }
    
    /**
     * Make space in the index for a new entry
     * 
     * @return int
     */
    public function shiftIndex($pindex)
    {
        // !! unfinished function, not yet able to update multiple entries
        //https://stackoverflow.com/questions/4337751/doctrine-2-update-query-with-query-builder
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->createQuery(
                "SELECT s "
                . " FROM TrackBundle:ProductStatus s"
                . " WHERE s.pindex <= :space"
        )->setParameter('space', $pindex);
        
        $result = $query->getResult();
        
        return false;
    }
}
