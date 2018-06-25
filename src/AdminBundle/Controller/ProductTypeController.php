<?php

namespace AdminBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Doctrine\ORM\EntityManagerInterface;
use TrackBundle\Entity\ProductType;
use TrackBundle\Entity\ProductTypeAttribute;

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
        
        return $this->render('AdminBundle:Type:index.html.twig', 
                array('products' => $products));
    }
    
    /**
     * @Route("/create", name="producttype_new")
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                "SELECT COUNT(pt.id) "
                . "FROM TrackBundle:ProductType pt")
                ->getResult();
        
        $index = $query[0][1]+1;
        
        $producttype = new ProductType();
        $producttype->setPindex($index);
        
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
            return $this->render('AdminBundle:Type:new.html.twig', array(
                'form' => $form->createView(),
            ));
        }
    }
    
    /**
     * @Route("/show/{id}", name="producttype_show")
     * @Method("GET")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine();
        
        // get product
        $producttype = $em->getRepository('TrackBundle:ProductType')
                ->find($id);
        
        // get all attributes with correct order
        $query = $em->getRepository('TrackBundle:Attribute')
                ->createQueryBuilder('a')
                ->orderBy('a.id', 'ASC')
                ->getQuery();
        
        $attributes = $query->getResult();
        
        return $this->render('AdminBundle:Type:show.html.twig', array(
            'producttype' => $producttype,
            'attributes' => $attributes,
        ));
    }
    
    /**
     * @Route("/edit/{id}", name="producttype_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(ProductType $producttype, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        // build basic form
        $editForm = $this->createFormBuilder($producttype)
                ->add('name', TextType::class)
                ->add('save', SubmitType::class)
                ->getForm();
        
        $editForm->handleRequest($request);
        
        // get all attributes
        $attributes = $em->getRepository('TrackBundle:Attribute')->findAll();
        
        // get type's attributes
        $typeattributes = $em->getRepository('TrackBundle:ProductTypeAttribute')
                ->findBy(array("typeId" => $producttype->getId()));
        
        if($editForm->isSubmitted() && $editForm->isValid()) {
            $em->persist($producttype);
            $em->flush();
            
            return $this->redirectToRoute('producttype_index');
        }
        
        // create attr name list (not quite done)
        /* 
        $attrList = [];
        foreach($attributes as $attr) {
            $attrList[$attr->getName()] = $attr->getId();
        }
        
        // foreach type attribute, show dropdown option
        foreach($typeattributes as $tAttr) {
            $editForm = $editForm->add('attribute_'.$tAttr->getId(), ChoiceType::class,[
                'choices'   => $attrList, 
                'mapped'    => false,
                ]
            );
        }*/
        
        return $this->render('AdminBundle:Type:edit.html.twig', array(
            'form' => $editForm->createView(),
            'producttype' => $producttype,
        ));
    }
    
    /**
     * Delete producttype, make sure no products are assigned to it
     * 
     * @Route("/delete/{id}", name="producttype_delete")
     * @Method("GET")
     */
    public function deleteAction($id) 
    {
        $em = $this->getDoctrine()->getManager();
        
        // check if any products have the type
        $producttype = $em->getRepository('TrackBundle:ProductType')
                ->find($id);
        
        // delete the type
        $em->remove($producttype);
        $em->flush();
        
        return $this->redirectToRoute('producttype_index');
    }
    
    /**
     * For creating new attributes for Product Types 
     * (affects templates, not existing products)
     * 
     * @Route("/edit/{id}/addattr", name="producttype_edit_attradd") 
     * @Method("GET")
     */
    public function addAttribute($id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $pt_attribute = new ProductTypeAttribute();
        $pt_attribute->setTypeId($id);
        $pt_attribute->setAttrId(1);
        
        $em->persist($pt_attribute);
        $em->flush();
        
        return $this->redirectToRoute('producttype_edit', 
            array('id' => $id)
        );
    }
    
    /**
     * Get all attributes that belong to this producttype
     */
    public function getAttribute(Product $product)
    {
        $em = $this->getDoctrine()->getManager();
        
        $typeattributes = $em->getRepository('TrackBundle:ProductTypeAttribute')
                ->findBy(array("typeId" => $this->getId()));
        
        $this->attributes = $typeattributes;
    }
}
