<?php


namespace TrackBundle\Controller;

use TrackBundle\Entity\Product;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Controller for editing multiple products in one form
 * 
 * @Route("/track/bulk")
 */
class BulkController extends ProductController
{
    /**
     * @Route("/edit", name="track_bulk_edit")
     * @Method({"GET"})
     */
    public function bulkEditAction(Request $request)
    {
        $ids = $request->query->get('id');
        
        $products = $this->getProductsByIds($ids);
        
        // if all items are the same type, give attribute options
        if($this->ifProductTypeEqual($products)) {
            foreach($products as $product) {
                $product->attributes = $this->getProductAttributes($product);
            }
        }
        
        foreach($products as $product) {
              $lastproduct = $product;
//            echo "<pre>";
//            print_r($product->attributes);
//            echo "</pre>";
        } 
        
        // on submit,
        // check if all products have attributes
        $editForm = $this->addProductDataToForm($lastproduct);
        
        $editForm->add('save', SubmitType::class, ['label' => 'Save Changes']);
      
        $editForm = $editForm->getForm();
       
        // create form for attributes
        
        
        return $this->render('TrackBundle:Bulk:edit.html.twig', array(
            'edit_form' => $editForm->createView(),
            'sellable' => PRODUCT_SELLABLE,
        ));
    }

    /**
     * Loops through array of products 
     * returns true if products are the same type
     * 
     * @param type $products
     * @return boolean
     */
    public function ifProductTypeEqual($products) {
        $equal = true; 
        $lasttype = ""; 
        
        $i=0; 
        foreach($products as $product) {
            if($i>0 && ($lasttype != $product->getType())) {
                $equal = false;
            }
            
            $lasttype = $product->getType();
            $i++;
        }
        
        return $equal;
    }
    
}
