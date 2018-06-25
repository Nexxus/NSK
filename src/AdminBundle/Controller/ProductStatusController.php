<?php

namespace AdminBundle\Controller;

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
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->createQuery(
                  ' SELECT '
                . '     s.id,'
                . '     s.pindex,'
                . '     s.name,'
                . '     p.id as exist'
                . ' FROM TrackBundle:ProductStatus s'
                . ' LEFT JOIN TrackBundle:Product p'
                . '     WITH p.status = s.id'
                . ' WHERE '
                . '     s.pindex < 999'
                . ' ORDER BY '
                . '     s.pindex ASC');
        $productstatus = $query->getResult();
        
        return $this->render('AdminBundle:Status:index.html.twig', array(
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

                //echo "<pre>";print_r($status);echo "</pre>";exit;

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
        
        return $this->render('AdminBundle:Status:new.html.twig', array(
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
     * @Route("/delete/{id}/{pindex}", name="status_delete")
     */
    public function deleteAction($id, $pindex)
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
                "DELETE FROM TrackBundle:ProductStatus s"
                . " WHERE s.id = :statusid"
                . "SET FOREIGN_KEY_CHECKS = 0;"
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
    public function shiftIndex($pindex, $method)
    {
        // !! unfinished function, not yet able to update multiple entries
        //https://stackoverflow.com/questions/4337751/doctrine-2-update-query-with-query-builder
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->createQuery(
                "SELECT s "
                . " FROM TrackBundle:ProductStatus s"
                . " WHERE s.pindex >= :space"
                . " AND s.name != :name"
        )->setParameter('space', $pindex)
         ->setParameter('name', "Sold");
        $statuses = $query->getResult();
        
        if($method == "add") {
            foreach($statuses as $status) {
                $query = $em->createQuery(
                        "UPDATE TrackBundle:ProductStatus s"
                        . " SET s.pindex = s.pindex+1"
                        . " WHERE s.id = :id"
                )->setParameter('id', $status->getId())
                ->getResult();
            }
        }
        if($method == "remove") {
            foreach($statuses as $status) {
                $query = $em->createQuery(
                        "UPDATE TrackBundle:ProductStatus s"
                        . " SET s.pindex = s.pindex-1"
                        . " WHERE s.id = :id"
                )->setParameter('id', $status->getId())
                ->getResult();
            }
        }
        return 0;
    }
}
