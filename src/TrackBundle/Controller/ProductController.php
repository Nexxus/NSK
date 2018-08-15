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

namespace TrackBundle\Controller;

const PRODUCT_SELLABLE = true;

use TrackBundle\Entity\Product;
use TrackBundle\Entity\ProductAttribute;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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

        $user = $this->get('security.token_storage')->getToken()->getUser();

        // retrieve search query
        $search_query = $request->request->get('item_search');

        // session for managing search queries
        $search_session = new Session();
        $search_session = $this->storeSearchQuery($search_query, $search_session);

        // clear search if requested
        $clear = $request->query->get('clear');
        if($clear==1) {
            $this->clearSearchQuery($search_session);
        }



        // get products
        $productquery = $em->getRepository('TrackBundle:Product')->createQueryBuilder('p')
                ->orderBy('p.'.$sort , $by)
                ->setFirstResult(($page - 1) * 10)
                ->setMaxResults(10);

        // load search query from slide menu
        $stored_query = $this->loadSearchQuery($search_session);
        $productquery = $this->searchSpecific($productquery, $stored_query);

        // if coming from admin panel, only show sold
        if($only=='sold') {
            $productquery->andWhere('p.status = 999');
        } else {
            $productquery->andWhere('p.status < 999 OR p.status IS NULL');
        }

        // Only admins and Copiatek people can see all products
        if($user->getLocation() !== null
                && !in_array('ROLE_ADMIN', $user->getRoles())
                && !in_array('ROLE_COPIA', $user->getRoles())
        ) {
            // convert location to ID using the manager
            $locid = $em->getUnitOfWork()->getEntityIdentifier($user->getLocation());
            $userloc = $locid['id'];

            $productquery->andWhere("p.location = ". $userloc);
        }

        $products = $productquery->getQuery()->getResult();

        // obtain data for the dropdowns
        $locations  = $em->getRepository('TrackBundle:Location')->findAll();
        $types      = $em->getRepository('TrackBundle:ProductType')->findAll();
        $brands     = $em->getRepository('TrackBundle:Brand')->findAll();

        return $this->render('TrackBundle:Track:index.html.twig', array(
            'products' => $products,
            'page' => $page,
            'searched' => $this->checkSearchQuery($search_session),
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
    public function newAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $product = new Product();
        $repository_product = $this->getDoctrine()->getRepository(Product::class);
        $query = $repository_product->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC')
            ->getQuery();
        $result = $query->getResult();
        if(count($result)>0) {
            $generatedsku = ($result[0]->getId() + 1);
        } else  {
            $generatedsku = "0";
        }
        $form = $this->createFormBuilder($product)
            ->add('checkbox', CheckboxType::class, array(
                'label' => ' ',
                'required' => false,
                'mapped' => false))
            ->add('sku')
            ->add('name')
            ->add('quantity')
            ->add('price')
            ->add('location')
            ->add('type')
            ->add('description')
            ->add('status')
            ->add('brand')
            ->add('department')
            ->add('owner', TextType::class, array(
                'required' => false
            ))
            ->add('saveAmount', IntegerType::class, [
                'mapped' => false,
                'attr' => [
                    'maxlength' => 3,
                    'value' => 1,
                ]
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('checkbox')->getData() === true) {
                if ($form->get('type')->getData()) {
                    $generatedsku = substr($form->get('type')->getData(), 0, 1) . $generatedsku;
                }
                $product->setSku($generatedsku);
            }
            $saveAmount = $form->get('saveAmount')->getData();
            if ($saveAmount > 0) {
                for($i=0;$i<$saveAmount;) {
                    $copy = clone $product;
                    $copy->setSku($copy->getSku() . $i);
                    if($this->checkExistingSku($copy->getSku() ) === true) {
                        $em->persist($copy);
                        $em->flush($copy);
                        $this->checkAttributeTemplate($copy);
                        $i++;
                    } else {
                        return $this->render('TrackBundle:Track:new.html.twig', array(
                            'product'       => $product,
                            'form'          => $form->createView(),
                            'error_msg'     => 'DuplicateSku',
                            'sellable'      => PRODUCT_SELLABLE,
                        ));
                    }
                }
                return $this->redirectToRoute('track_index');
            } else {
                return $this->render('TrackBundle:Track:new.html.twig', array(
                    'product'       => $product,
                    'form'          => $form->createView(),
                    'error_msg'     => 'WrongInput',
                    'sellable'      => PRODUCT_SELLABLE,
                ));
            }
        }

        return $this->render('TrackBundle:Track:new.html.twig', array(
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

        // get attributes (previously checked or added)
        $attributes = $this->getProductAttributes($product);

        return $this->render('TrackBundle:Track:show.html.twig', array(
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

        // if user isn't allowed to be here, redirect
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if(!$this->checkUserLocRights($user, $product->getLocation())) {
            return $this->redirectToRoute("track_index");
        }

        echo $product->getLocation();
        echo $user->getLocation();


        // create form for editing
        $editForm = $this->createFormBuilder($product)
                ->add('sku', TextType::class)
                ->add('name', TextType::class)
                ->add('quantity', IntegerType::class, array(
                    'required' => false
                ))
                ->add('price', IntegerType::class, array(
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

        return $this->render('TrackBundle:Track:edit.html.twig', array(
            'product' => $product,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'sellable' => PRODUCT_SELLABLE,
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

         // if user isn't allowed to be here, redirect
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if(!$this->checkUserLocRights($user, $product->getLocation())) {
            return $this->redirectToRoute("track_index");
        }

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
     * Get a product by a SKU
     *
     * @param type $sku
     * @return product
     * @return boolean
     */
    public function getBySku($sku) {
        $em = $this->getDoctrine()->getManager();

        $product = $em->getRepository('TrackBundle:Product')
                ->findOneBySku($sku);

        if($product) {
            return $product;
        }
        else
        {
            return false;
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

    public function getProductAttributes(Product $product) {
        $em = $this->getDoctrine()->getManager();

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

        return $attributes;
    }

    /*
     * Returns true if a SKU in the database is not taken
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

    /**
     * Returns products with given ids
     *
     * @param array $ids
     * @return products
     */
    public function getProductsByIds($ids) {
        $em = $this->getDoctrine()->getManager();

        // get items by GET ids
        $products = $em->getRepository("TrackBundle:Product")->createQueryBuilder('q');

        $whereIn  = "(";

        foreach($ids as $id) {
            $whereIn .= $id . ",";
        }

        $whereIn = rtrim($whereIn, ",") . ")";

        $products = $products->where("q.id IN ".$whereIn);

        $products = $products->getQuery()->getResult();

        return $products;
    }

    public function addAttributesToProductForm(Form $editForm) {

        $em = $this->getDoctrine()->getManager();

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

        $idArr = [];

        // add the attributes to the form
        foreach($attributes as $attribute) {
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
    }

    /**
     * Find specific products on search
     */
    public function searchSpecific($productquery, $search_query) {
        if(isset($search_query['searchbar']) && $search_query['searchbar']<>'') {
            $productquery->andWhere($productquery->expr()->orX(
                    $productquery->expr()->like('p.id', ':q'),
                    $productquery->expr()->like('p.sku', ':q'),
                    $productquery->expr()->like('p.name', ':q'),
                    $productquery->expr()->like('p.description', ':q')
                ))
                ->setParameter('q', '%'.$search_query['searchbar'].'%');
        }

        if(isset($search_query['spec'])) {
            // check for location
            if(isset($search_query['spec']['location']) && $search_query['spec']['location']!=null) {
                $productquery->andWhere('p.location = :q')
                        ->setParameter('q', $search_query['spec']['location']);
            } else {
                $search_query['spec']['location'] = null;
            }

            // check for type
            if(isset($search_query['spec']['type']) && $search_query['spec']['type']!=null) {
                $productquery->andWhere('p.type = :q')
                        ->setParameter('q', $search_query['spec']['type']);
            } else {
                $search_query['spec']['type'] = null;
            }
        }

        return $productquery;
    }

    /**
     * Store search query into session
     *
     * @param type $q
     * @param Session $s
     * @return Session
     */
    public function storeSearchQuery($q, Session $s) {
        if(isset($q['searchbar']) && $q['searchbar']<>'') {
            $s->set('searchbar', $q['searchbar']);
        }
        if(isset($q['spec']['location']) && $q['spec']['location']!=null) {
            $s->set('spec_location', $q['spec']['location']);
        }

        if(isset($q['spec']['type']) && $q['spec']['type']!=null) {
            $s->set('spec_type', $q['spec']['type']);
        }

        return $s;
    }

    /**
     * Loads stored query into array for further use
     *
     * @param type $s
     * @return type
     */
    public function loadSearchQuery(Session $s) {
        //echo "<pre>"; print_r($s); echo "</pre>";
        $arr = [];

        if($s->has('searchbar')) {
            $arr['searchbar']         = $s->get('searchbar');
        }
        if($s->has('spec_location')) {
            $arr['spec']['location']  = $s->get('spec_location');
        }
        if($s->has('spec_type')) {
            $arr['spec']['type']      = $s->get('spec_type');
        }

        return $arr;
    }

    /**
     * Check if search query is active
     *
     * @param Session $s
     * @return boolean
     */
    public function checkSearchQuery(Session $s) {
        $bool = false;

        if($s->has('searchbar')) {
            $bool = true;
        }
        if($s->has('spec_location')) {
            $bool = true;
        }
        if($s->has('spec_type')) {
            $bool = true;
        }

        return $bool;
    }

    /**
     * Clear search query
     *
     * @param Session $s
     */
    public function clearSearchQuery(Session $s) {
        $s->remove('searchbar');
        $s->remove('spec_location');
        $s->remove('spec_type');

        return $this->redirectToRoute('track_index');
    }

    /**
     * Check if user has rights, returns true if admin or copiatek user
     */
    public function checkUserLocRights($user, $loc) {
        if($user->getLocation() == $loc
                || in_array('ROLE_ADMIN', $user->getRoles())
                || in_array('ROLE_COPIA', $user->getRoles()))
        {
            return true;
        } else {
            return false;
        }
    }
}
