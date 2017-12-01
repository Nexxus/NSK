<?php

namespace TrackBundle\Controller;


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
        
        return $this->render('admin/type/show.html.twig', array(
            'producttype' => $producttype,
            'attributes' => $attributes,
        ));
    }
    
    /**
     * @Route("/edit/{id}", name="producttype_edit")
     */
    public function editAction(ProductType $producttype)
    {
        $em = $this->getDoctrine();
        
        // get all attributes
        $attributes = $em->getRepository('TrackBundle:Attribute')->findAll();
        
        // get type's attributes
        $typeattributes = $em->getRepository('TrackBundle:ProductTypeAttribute')
                ->findBy(array("typeId" => $producttype->getId()));
        
        // build basic form
        $editForm = $this->createFormBuilder($producttype)
                ->add('name', TextType::class)
                ->add('save', SubmitType::class);
        
        // create attr name list
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
        }
        
        $editForm = $editForm->getForm();
        
        return $this->render('admin/type/edit.html.twig', array(
            'form' => $editForm->createView(),
            'producttype' => $producttype,
        ));
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
