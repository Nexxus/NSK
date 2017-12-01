<?php

namespace TrackBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Doctrine\ORM\EntityManagerInterface;
use TrackBundle\Entity\ProductType;

/**
 * @Route("admin/type")
 */
class ProductTypeController extends Controller
{
    /**
     * @Route("/", name="producttype_index") 
     */
    public function indexAction() 
    {
        $em = $this->getDoctrine();
        $products = $em->getRepository('TrackBundle:ProductType')
                        ->findAll();
        
        return $this->render('admin/type/index.html.twig', 
                array('products' => $products));
    }
    
    /**
     * @Route("/create", name="producttype_new")
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $producttype = new ProductType();
        
        $form = $this->createFormBuilder($producttype)
                    ->add('name', TextType::class)
                    ->add('pindex', IntegerType::class)
                    ->add('comment', TextType::class, array('required' => false))
                    ->add('save', SubmitType::class, array('label' => 'Create Type'))
                    ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            $producttype = $form->getData();
            
            $em->persist($producttype);
            $em->flush();
            
            return $this->redirectToRoute('producttype_index', ["order" => "Product Type has been saved."]);
        } 
        else 
        {
            return $this->render('admin/type/new.html.twig', array(
                'form' => $form->createView(),
            ));
        }
    }
}
