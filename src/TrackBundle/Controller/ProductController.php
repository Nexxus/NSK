<?php

namespace TrackBundle\Controller;

const PRODUCT_SELLABLE = true;

use TrackBundle\Entity\Product;
use TrackBundle\Entity\ProductAttribute;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Product controller.
 *
 * @Route("track")
 */
class ProductController extends Controller
{   
    /**
     * Lists all product entities.
     *
     * @Route("/index/{page}/{sort}/{by}/{only}/{spec}", name="track_index", defaults={"page" = 1, "sort" = "updatedAt", "by" = "DESC"})
     * @Method({"GET", "POST"})
     */
    public function indexAction(Request $request, $sort, $by, $page = 1, $only = null, $spec = null)
    {
        $em = $this->getDoctrine()->getManager();
        
        // true if search query was made
        $searched = false;
        
        $search_query = $request->request->get('item_search');
        
        // show search post
//        if(isset($search_query)) {
//            echo "<pre>"; print_r($search_query); echo "</pre>";
//        }
        
        $locations  = $em->getRepository('TrackBundle:Location')->findAll();
        $types      = $em->getRepository('TrackBundle:ProductType')->findAll();
        $brands     = $em->getRepository('TrackBundle:Brand')->findAll();
        
        // get products
        $productquery = $em->getRepository('TrackBundle:Product')->createQueryBuilder('p')
                ->orderBy('p.'.$sort , $by)
                ->setFirstResult(($page - 1) * 10)
                ->setMaxResults(10);
        
        // search terms through id, sku, name, description
        if(isset($search_query['searchbar']) && $search_query['searchbar']<>'') {
            $searched = true;
            $productquery->andWhere($productquery->expr()->orX(
                    $productquery->expr()->like('p.id', ':q'),
                    $productquery->expr()->like('p.sku', ':q'),
                    $productquery->expr()->like('p.name', ':q'),
                    $productquery->expr()->like('p.description', ':q')
                ))
                ->setParameter('q', '%'.$search_query['searchbar'].'%');
        }
        
        if(isset($search_query['spec'])) 
        {
            $searched = true;
            // location
            if(isset($search_query['spec']['location'])) {
                $productquery->andWhere('p.location = :q')
                        ->setParameter('q', $search_query['spec']['location']);
            }
            
            // type
            if(isset($search_query['spec']['type'])) {
                $productquery->andWhere('p.type = :q')
                        ->setParameter('q', $search_query['spec']['type']);
            }
        }
        
        // if coming from admin panel, only show sold
        if($only=='sold') {
            $productquery->andWhere('p.status = 999');
        } else {
            $productquery->andWhere('p.status < 999 OR p.status IS NULL');
        }
        
        
        $products = $productquery->getQuery()->getResult();

        return $this->render('product/index.html.twig', array(
            'products' => $products,
            'page' => $page,
            'searched' => $searched,
            'search_options' => [
                'specifics' => [
                    'locations' => $locations,
                    'types' => $types,
                    'brands' => $brands
                ],
            ],
            'search_query' => [
                'terms' => $search_query['searchbar'],
                'locations' => $search_query['spec']['location'],
                'types' => $search_query['spec']['type'],
            ]
        ));
    }

    /**
     * Creates a new product entity.
     *
     * @Route("/new", name="track_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $product = new Product();
        
        // get last id
        $repository_product = $this->getDoctrine()->getRepository(Product::class);
        
        $query = $repository_product->createQueryBuilder('p')
                ->orderBy('p.id', 'DESC')
                ->getQuery();
                
        $result = $query->getResult();
        $generatedsku = "Copia" . ($result[0]->getId() + 1);
        
        $form = $this->createFormBuilder($product)
                ->add('sku', TextType::class, ['attr' => [
                        'value' => $generatedsku,
                    ]
                ])
                ->add('name')
                ->add('quantity')
                ->add('location')
                ->add('type')
                ->add('description')
                ->add('status')
                ->add('brand')
                ->add('department')
                ->add('owner')
                ->getForm();
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // check for sku
            if($this->checkExistingSku($product->getSku() ))
            {
                $em->persist($product);
                $em->flush($product);
                
                // add potential attributes
                $this->checkAttributeTemplate($product);

                return $this->redirectToRoute('track_show', array('id' => $product->getId()));
            } 
             else 
            {
                return $this->render('product/new.html.twig', array(
                    'product'       => $product,
                    'form'          => $form->createView(),
                    'error_msg'     => 'DuplicateSku'
                ));
            }
        }

        return $this->render('product/new.html.twig', array(
            'product' => $product,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a product entity.
     *
     * @Route("/{id}/show", name="track_show")
     * @Method("GET")
     */
    public function showAction(Product $product)
    {
        $deleteForm = $this->createDeleteForm($product);
        
        // get attributes
        $em = $this->getDoctrine()->getManager();
        
        // get attributes (previously checked or added)
        $query = $em->createQuery('SELECT'
                . '     pa.id,'
                . '     a.name,'
                . '     pa.value'
                . ' FROM'
                . '     TrackBundle:ProductAttribute pa '
                . ' LEFT JOIN TrackBundle:Attribute a '
                . '     WITH pa.attrId = a.id '
                . 'WHERE '
                . '     pa.productid = :id')
                ->setParameter('id', $product->getId());
        $attributes = $query->getResult();

        return $this->render('product/show.html.twig', array(
            'product' => $product,
            'attributes' => $attributes,
            'delete_form' => $deleteForm->createView(),
            'sellable'      => PRODUCT_SELLABLE,
        ));
    }

    /**
     * Displays a form to edit an existing product entity.
     *
     * @Route("/{id}/edit", name="track_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Product $product)
    {
        $em = $this->getDoctrine()->getManager();

        // create form for deleting
        $deleteForm = $this->createDeleteForm($product);
        
        // create form for editing
        $editForm = $this->createFormBuilder($product)
                ->add('sku', TextType::class)
                ->add('name', TextType::class)
                ->add('quantity', IntegerType::class, array(
                    'required' => false
                ))
                ->add('location',  EntityType::class, array(
                    'class' => 'TrackBundle:Location',
                    'choice_label' => 'name'
                ))
                ->add('type',  EntityType::class, array(
                    'class' => 'TrackBundle:ProductType',
                    'choice_label' => 'name'
                ))
                ->add('description', TextType::class, array(
                    'required' => false
                ))
                ->add('status')
                ->add('brand', TextType::class, array(
                    'required' => false
                ))
                ->add('department', TextType::class, array(
                    'required' => false
                ))
                ->add('owner', TextType::class, array(
                    'required' => false
                ));
        
        // get attributes (previously checked or added)
        $query = $em->createQuery('SELECT'
                . '     pa.id,'
                . '     a.attr_code,'
                . '     a.name,'
                . '     pa.value'
                . ' FROM'
                . '     TrackBundle:ProductAttribute pa '
                . ' LEFT JOIN TrackBundle:Attribute a '
                . '     WITH pa.attrId = a.id '
                . 'WHERE '
                . '     pa.productid = :id')
                ->setParameter('id', $product->getId());
        $attributes = $query->getResult();
        
        $attribute_form = $this->createFormBuilder("");
        $attribute_count = 0;
        
        $idArr = [];
        
        // add the attributes to the form
        foreach($attributes as $attribute) {
            $attribute_count++;
            $idArr[] = $attribute['id'];
            
            $fieldid = "attribute_" . $attribute['id'];
            $fieldname = $attribute['attr_code'];
            $fieldlabel = $attribute['name'];
            $fieldvalue = $attribute['value'];
            
            $editForm->add($fieldid, TextType::class, [
                'mapped'    => false,
                'label'     => $fieldlabel,
                'required'  => false,
                'attr'      => [
                    'id'        => $fieldid,
                    'value'     => $fieldvalue,
                ],
            ]);
        }
        
        $editForm->add('save', SubmitType::class, ['label' => 'Save Changes']);
        
        $editForm = $editForm->getForm();
                
        $editForm->handleRequest($request);
        
        // if product has type, check if it needs attributes
        $this->checkAttributeTemplate($product);
        
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            
            // check for sku
            $skuquery = $em->createQuery(
                    'SELECT p.sku'
                    . ' FROM TrackBundle:Product p'
                    . ' WHERE p.sku = :givensku'
                    . ' AND p.id <> :id')
                    ->setParameter('givensku', $product->getSku())
                    ->setParameter('id', $product->getId());
            $result = $skuquery->getResult();
            
            $attributeArr = [];
            
            // put attributes in array
            foreach($idArr as $id) {
                  $attributeArr[$id] = [
                      'id'    => $id,
                      'value' => $editForm->get('attribute_' . $id)->getData()
                  ];
            }
            
            if(count($result)==0)
            {
                // save product
                $em->persist($product);
                $em->flush($product);
                
                // save attributes
                foreach($attributeArr as $attr){
                    $query = $em->createQuery(
                              'UPDATE '
                            . '     TrackBundle:ProductAttribute pa'
                            . ' SET   pa.value = :value'
                            . ' WHERE pa.id  = :id')
                            ->setParameter('value', $attr['value'])
                            ->setParameter('id', $attr['id']);
                    $result = $query->getResult();
                } 
                
                return $this->redirectToRoute('track_show', array('id' => $product->getId()));
            } 
            else 
            {
                return $this->redirectToRoute('track_edit', array('id' => $product->getId()));
            }
        }
        
        return $this->render('product/edit.html.twig', array(
            'product' => $product,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'attributes' => $attributes,
            'attribute_count' => $attribute_count,
        ));
    }
    
    /**
     * Deletes a product entity.
     *
     * @Route("/{id}", name="track_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Product $product)
    {
        $form = $this->createDeleteForm($product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush($product);
        }

        return $this->redirectToRoute('track_index');
    }

    /**
     * Retrieves an SKU and gets an item
     * 
     * @Route("/s/sku/{sku}", name="track_searchSku")
     * @Method("GET")
     */
    public function searchBySku($sku) {
        $em = $this->getDoctrine()->getManager();
        
        $product = $em->getRepository('TrackBundle:Product')
                ->findOneBySku($sku);
        if($product) {
            $id = $product->getId();  
        
            return $this->redirectToRoute("track_show", array('id' => $id));
        } 
        else
        {
            return $this->redirectToRoute("track_index",
                    array('err' => 'nif'));
        }
        
    }
    
    /**
     * Creates a form to delete a product entity.
     *
     * @param Product $product The product entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Product $product)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('track_delete', array('id' => $product->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    /**
     * Check if any attributes need to be added to a product
     * 
     * @param Product $product
     */
    private function checkAttributeTemplate(Product $product) {
        if($product->getType() != null) {
            $attr_repository = $this->getDoctrine()->getRepository(ProductAttribute::class);
            
            // check for attributes
            $query = $attr_repository->findBy(
                    ['productid' => $product->getId()]
            );

            if(count($query)==0) {   
                $this->applyAttributeTemplate($product);   
            }
        }
    }
    
    /**
     * Add attributes to product
     */
    private function applyAttributeTemplate(Product $product) {
        $em = $this->getDoctrine()->getManager();
        
        // get id to insert into productattr rows
        $type_id = $product->getType();
        
        // find matching attributes to add
        $query = $em->createQuery(''
                . 'SELECT'
                . '     pta.id, '
                . '     IDENTITY(pta.attrId) as attrid, '
                . '     pta.typeId, '
                . '     a.name'
                . ' FROM TrackBundle:ProductTypeAttribute pta'
                . ' LEFT JOIN TrackBundle:Attribute a '
                . '     WITH pta.attrId = a.id'
                . ' WHERE'
                . '     pta.typeId = :type_id')
                ->setParameter('type_id', $type_id);
        $result = $query->getResult();
        
        // apply empty attributes to product
        foreach($result as $attr) {
            $prod_attr = new ProductAttribute();
            $prod_attr->setProductid($product->getId());
            $prod_attr->setAttrId($attr['attrid']);
            
            $em->persist($prod_attr);
        }
        
        $em->flush();
        
    }
    
    /*
     * Returns true if a SKU in the database is taken
     */
    public function checkExistingSku($sku) {
        $em = $this->getDoctrine()->getManager();
        
        $skuquery = $em->createQuery(
                    'SELECT p.sku'
                    . ' FROM TrackBundle:Product p'
                    . ' WHERE p.sku = :givensku')
                    ->setParameter('givensku', $sku);
        $result = $skuquery->getResult();
        
        return (count($result) == 0);
    }
    
    
}
