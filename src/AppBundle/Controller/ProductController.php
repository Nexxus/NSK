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

namespace AppBundle\Controller;

const PRODUCT_SELLABLE = true;

use AppBundle\Entity\Attribute;
use AppBundle\Entity\Product;
use AppBundle\Entity\ProductAttributeRelation;
use AppBundle\Form\ProductType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

/**
 * Product controller.
 *
 * @Route("/track/products")
 */
class ProductController extends Controller
{
    /**
     * Lists all product entities.
     *
     * @Route("/index/{page}/{sort}/{by}/{only}/{spec}", name="track_index", defaults={"page" = 1, "sort" = "id", "by" = "DESC"})
     * @Method({"GET", "POST"})
     */
    public function indexAction(Request $request, $sort, $by, $page = 1, $only = null, $spec = null)
    {
        $em = $this->getDoctrine()->getManager();

        // retrieve message
        $msg = $request->query->get('msg');

        // retrieve search query
        $search_query = $request->request->get('item_search');

        $search_query1 = $request->request->get('query');

        // session for managing search queries
        $search_session = new Session();
        $search_session = $this->storeSearchQuery($search_query, $search_session);

        // clear search if requested
        $clear = $request->query->get('clear');
        if($clear==1) {
            $this->clearSearchQuery($search_session);
        }

        // get products
        $productquery = $em->getRepository('AppBundle:Product')->createQueryBuilder('p')
                ->orderBy('p.'.$sort , $by)
                ->setFirstResult(($page - 1) * 10)
                ->setMaxResults(10);

        // load search query from slide menu
        $stored_query = $this->loadSearchQuery($search_session);
        $productquery = $this->searchSpecific($productquery, $stored_query);
        $products = $productquery->getQuery()->getResult();

        // obtain data for the dropdowns
        $locations  = $em->getRepository('AppBundle:Location')->findAll();
        $types      = $em->getRepository('AppBundle:ProductType')->findAll();

        return $this->render('AppBundle:Track:index.html.twig', array(
            'products' => $products,
            'msg' => $msg,
            'page' => $page,
            'searched' => $this->checkSearchQuery($search_session),
            'search_options' => [
                'specifics' => [
                    'locations' => $locations,
                    'types' => $types
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
     */
    public function newAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $product = new Product();
        $repository_product = $this->getDoctrine()->getRepository(Product::class);
        $query = $repository_product->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC')
            ->getQuery();
        $result = $query->getResult();

        $locCheck = $em->createQuery(
                'SELECT l'
                . ' FROM AppBundle:Location l')
                ->getResult();
        if(!$locCheck) {
            return $this->redirectToRoute(
                'track_index', array(
                    'msg' => 'No locations made yet',
                )
            );
        }

        $typeCheck = $em->createQuery(
                'SELECT pt'
                . ' FROM AppBundle:ProductType pt')
                ->getResult();
        if(!$typeCheck) {
            return $this->redirectToRoute(
                'track_index', array(
                    'msg' => 'No types made yet',
                )
            );
        }
        // Check if necessary constants are filled

        if(count($result)>0) {
            $generatedsku = ($result[0]->getId() + 1);
        } else  {
            $generatedsku = "0";
        }

        $form = $this->createFormBuilder($product)

            ->add('generatesku', ChoiceType::class, [
                'mapped' => false,
                'choices' => [
                    'Yes' => 'Yes',
                    'No' => 'No'
                ],
                'attr' => [
                    'id' => 'generateSkuSelect'
                ],
                'data' => 'Yes',
            ])
            ->add('sku', TextType::class, [
                'required' => false,
            ])
            ->add('name')
            ->add('quantity')
            ->add('price')
            ->add('location')
            ->add('type')
            ->add('description')
            ->add('saveAmount', IntegerType::class, [
                'mapped' => false,
                'attr' => [
                    'maxlength' => 3,
                    'value' => 1,
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        // submit product form
        if ($form->isSubmitted() && $form->isValid())
        {
            // generate sku if requested, or if sku is null
            if ($form->get('generatesku')->getData() === 'Yes' || $product->getSku() === null)
            {
                $product = $this->generateNewSku($product);
            }
            $saveAmount = $form->get('saveAmount')->getData();

            for($i=0;$i<$saveAmount;$i++) {
                $copy = clone $product;

                if($saveAmount > 1) {
                    $copy->setSku($copy->getSku() . $i);
                }

                // if product has type, check if it needs attributes
                if($this->checkFreeSku($copy->getSku() ) === true) {
                    $em->persist($copy);
                    $em->flush($copy);
                } else {
                    return $this->render('AppBundle:Track:new.html.twig', array(
                        'product'       => $product,
                        'form'          => $form->createView(),
                        'error_msg'     => 'DuplicateSku',
                        'sellable'      => PRODUCT_SELLABLE,
                    ));
                }
            }
            return $this->redirectToRoute('track_index');
        }

        return $this->render('AppBundle:Track:new.html.twig', array(
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

        return $this->render('AppBundle:Track:show.html.twig', array(
            'product' => $product,
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

        // if product has type, check if it needs attributes
        $this->applyAttributeTemplate($product);

        // load forms
        $editForm = $this->createForm(ProductType::class, $product);
        $deleteForm = $this->createDeleteForm($product);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid())
        {
            $newAttribute = $editForm['newAttribute']->getData();

            // check if attribute exists, don't add if it doesn't
            if(isset($newAttribute))
            {
               $alreadyExists = $product->containsAttributeRelation($newAttribute->getId());
            }

            // add new attribute
            if ($newAttribute && !$alreadyExists)
            {
                $newAttributeRelation = new ProductAttributeRelation();
                $newAttributeRelation->setAttribute($newAttribute);
                $newAttributeRelation->setProduct($product);
                $newAttributeRelation->setValue('');
                $newAttributeRelation->setQuantity(1);
                $product->addAttributeRelation($newAttributeRelation);
            }

            // save product
            $em->persist($product);
            $em->flush();

            /*return $this->redirectToRoute('track_edit',
                array('id' => $product->getId())
            );*/
            return $this->redirectToRoute('track_index');

        }

        return $this->render('AppBundle:Track:edit.html.twig', array(
            'product' => $product,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'sellable' => PRODUCT_SELLABLE,
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

        $product = $em->getRepository('AppBundle:Product')
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

        $product = $em->getRepository('AppBundle:Product')
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
            ->getForm();
    }

    /**
     * Check if any attributes need to be added to a product
     *
     * @param Product $product
     */
    private function applyAttributeTemplate(Product $product)
    {
        $em = $this->getDoctrine()->getManager();

        if($product->getType()) {
            // check if product already has attributes
            $missing = $this->compareAttributeTemplate($product);

            // add missing attributes
            foreach($missing as $attr) {
                $newAttributeRelation = new ProductAttributeRelation();
                $newAttributeRelation->setProduct($product);
                $newAttributeRelation->setAttribute($attr);
                $newAttributeRelation->setValue('');
                $newAttributeRelation->setQuantity(1);
                $product->addAttributeRelation($newAttributeRelation);
            }

            // save product
            $em->persist($product);
            $em->flush($product);
        }
    }

    /*
     * Compares attributes of a product and product type, returns missing attributes
     */
    private function compareAttributeTemplate(Product $product)
    {
        // get product attributes
        $prodAttrs = $product->getAttributeRelations();
        $type = $product->getType();
        $prodAttributeArr = [];

        foreach($prodAttrs->toArray() as $attr) {
            $prodAttributeArr[] = $attr->getAttribute()->getId();
        }

        // get type attributes
        $typeAttrs = $product->getType()->getAttributes();
        $typeAttributeArr = [];
        foreach($typeAttrs->toArray() as $attr) {
            $typeAttributeArr[] = $attr->getId();
        }

        $diff = array_diff($typeAttributeArr, $prodAttributeArr);

        // get missing attributes
        $attributes = $this->getDoctrine()->getRepository(Attribute::class)
                        ->findBy(['id' => $diff]);

        return $attributes;
    }

    /*
     * Returns true if a SKU in the database is free
     */
    public function checkFreeSku($sku) {
        $em = $this->getDoctrine()->getManager();

        $skuquery = $em->createQuery(
                    'SELECT p.sku'
                    . ' FROM AppBundle:Product p'
                    . ' WHERE p.sku = :givensku')
                    ->setParameter('givensku', $sku);
        $result = $skuquery->getResult();

        return (count($result) == 0);
    }

    /*
     * Generates new SKU, avoids duplicates
     */
    public function generateNewSku(Product $product)
    {
        $num = $product->getId();
        $gsku = $product->getSku();

        // if type is set, add prefix
        if ($product->getType())
        {
            $gsku = substr($product->getType(), 0, 1) . $gsku;
        }

        // increment if taken
        $free = $this->checkFreeSku($gsku);
        while(!$free) {
            $num++;
            $gsku = substr($product->getType(), 0, 1) . $num;
            $free = $this->checkFreeSku($gsku);
        }

        $product->setSku($gsku);
        return $product;
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
        $products = $em->getRepository("AppBundle:Product")->createQueryBuilder('q');

        $whereIn  = "(";

        foreach($ids as $id) {
            $whereIn .= $id . ",";
        }

        $whereIn = rtrim($whereIn, ",") . ")";

        $products = $products->where("q.id IN ".$whereIn);

        $products = $products->getQuery()->getResult();

        return $products;
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
