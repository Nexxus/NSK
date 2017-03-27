<?php

namespace TrackBundle\Controller;

use TrackBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

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
     * @Route("/index/{page}/{sortBy}", name="track_index", defaults={"page" = 1, "sortBy" = "id=ASC"})
     * @Method("GET")
     */
    public function indexAction($page, $sortBy)
    {
        $em = $this->getDoctrine()->getManager();
        
        $order = $em->getRepository('TrackBundle:Product')->serializeSort($sortBy);
        $products = $em->getRepository('TrackBundle:Product')->findSpecific($order);

        return $this->render('product/index.html.twig', array(
            'products' => $products,
        ));
    }
    
    /**
     * Prints a barcode PDF page
     * 
     * @Route("{id}/printBarcode", name="track_print_barcode")
     */
    public function printAction()
    {
        //$mpdf = new Mpdf();
        return new Response(
                'Hello World!'
        );
    }

    /**
     * Creates a new product entity.
     *
     * @Route("/new", name="track_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $product = new Product();
        $form = $this->createForm('TrackBundle\Form\ProductType', $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            // check for sku
            $skuquery = $em->createQuery(
                    'SELECT p.sku'
                    . ' FROM TrackBundle:Product p'
                    . ' WHERE p.sku = :givensku')
                    ->setParameter('givensku', $product->getSku());
            $result = $skuquery->getResult();
            
            if(count($result)==0)
            {
                $em->persist($product);
                $em->flush($product);
            
                // fill in product id if sku is left blank
            
                //

                return $this->redirectToRoute('track_show', array('id' => $product->getId()));
            } 
            else 
            {
                return $this->render('product/new.html.twig', array(
                    'product' => $product,
                    'form' => $form->createView(),
                    'error_msg' => 'DuplicateSku',
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

        return $this->render('product/show.html.twig', array(
            'product' => $product,
            'delete_form' => $deleteForm->createView(),
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
        $deleteForm = $this->createDeleteForm($product);
        $editForm = $this->createForm('TrackBundle\Form\ProductType', $product);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // check for sku
            $skuquery = $em->createQuery(
                    'SELECT p.sku'
                    . ' FROM TrackBundle:Product p'
                    . ' WHERE p.sku = :givensku'
                    . ' AND p.id <> :id')
                    ->setParameter('givensku', $product->getSku())
                    ->setParameter('id', $product->getId());
            $result = $skuquery->getResult();
            
            if(count($result)==0)
            {
                $em->persist($product);
                $em->flush($product);
            
                // fill in product id if sku is left blank
            
                //

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
    
    
}
