<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use FOS\RestBundle\View\View;
use AppBundle\Entity\PurchaseOrder;
use AppBundle\Entity\OrderStatus;
use AppBundle\Entity\Product;
use AppBundle\Entity\ProductAttributeRelation;
use AppBundle\Entity\ProductAttributeFile;
use AppBundle\Entity\Attribute;

class ApiController extends FOSRestController
{
    use UploadControllerTrait;

    /**
     * @Rest\Get("/purchaseorders/{statusId}")
     */
    public function getPurchaseOrdersAction($statusId)
    {
        $repo = $this->getDoctrine()->getRepository(PurchaseOrder::class);

        $orders = $repo->findMineByStatus($this->getUser(), $statusId);

        if ($orders === null)
        {
            return new View("No orders found", Response::HTTP_NO_CONTENT);
        }

        return $orders;
    }

    /**
     * @Rest\Put("/purchaseorderstatus")
     */
    public function updateStatusAction(Request $request)
    {
        $purchaseOrderId = $request->get('purchaseOrderId');
        $statusId = $request->get('statusId');

        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository(PurchaseOrder::class);

        /** @var PurchaseOrder */
        $order = $repo->findMineById($this->getUser(), $purchaseOrderId);

        if ($order === null)
        {
            return new View("No order found", Response::HTTP_NO_CONTENT);
        }

        $order->setStatus($em->getReference(OrderStatus::class, $statusId));

        $em->flush();

        return new View("Order updated successfully", Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/productattachment")
     */
    public function postAttachmentAction(Request $request)
    {
        /** @var UploadedFile */
        $file = $request->files->get('attachment');
        $productId = $request->get('productId');
        $attributeId = $request->get('attributeId');

        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository(Product::class);

        /** @var Product */
        $product = $em->find(Product::class, $productId);

        if ($product === null)
        {
            return new View("No product found", Response::HTTP_NO_CONTENT);
        }

        $repo->generateProductAttributeRelations($product);

        $relation = $product->getAttributeRelations()->filter(function (ProductAttributeRelation $r) use ($attributeId) {
            return $r->getAttribute()->getId() == $attributeId && $r->getAttribute()->getType() == Attribute::TYPE_FILE;
        })->first();

        if (!$relation)
        {
            return new View("No attribute found", Response::HTTP_NO_CONTENT);
        }
        elseif (!$request->files->count())
        {
            return new View("No attachment found", Response::HTTP_NO_CONTENT);
        }
        elseif (!$this->isImage($file->getRealPath()))
        {
            return new View("File is not an image", Response::HTTP_NOT_ACCEPTABLE);
        }

        $serverFilename = uniqid();
        $file->move($this->getFullUploadFolder(), $serverFilename);

        $attrfile = new ProductAttributeFile($product, $file->getClientOriginalName(), $serverFilename);
        $em->persist($attrfile);
        $em->flush($attrfile);

        $val = $relation->getValue() ? $relation->getValue() . "," . $attrfile->getId() : $attrfile->getId();
        $relation->setValue($val);

        $em->persist($relation);
        $em->flush();

        return new View("Attachment saved successfully", Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/product")
     */
    public function postProductAction(Request $request)
    {
        $sku = $request->get('sku');
        $name = $request->get('name');
        $retailPrice = $request->get('retailPrice');
        $purchasePrice = $request->get('purchasePrice');
        $description = $request->get('description');
        $purchaseOrderId = $request->get('purchaseOrderId');
        $quantity = $request->get('quantity') ?? 1;
        $typeId = $request->get('typeId');
        $statusId = $request->get('statusId');
        $locationId = $request->get('locationId');

        if (!$name || !$typeId || !$purchaseOrderId)
        {
            return new View("One or more required fields are missing: name a/o purchaseOrderId a/o typeId", Response::HTTP_NO_CONTENT);
        }
        elseif ($retailPrice && !is_numeric($retailPrice))
        {
            return new View("Retail price is not a number.", Response::HTTP_NO_CONTENT);
        }
        elseif ($purchasePrice && !is_numeric($purchasePrice))
        {
            return new View("Purchase price is not a number.", Response::HTTP_NO_CONTENT);
        }

        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository(Product::class);

        $type = $em->getReference(\AppBundle\Entity\ProductType::class, $typeId);
        $purchaseOrder = $this->getDoctrine()->getRepository(PurchaseOrder::class)->findMineById($this->getUser(), $purchaseOrderId);
        $status = $statusId ? $em->getReference(\AppBundle\Entity\ProductStatus::class, $statusId) : null;

        if (!$type || !$purchaseOrder)
        {
            return new View("One or more required fields are invalid: purchaseOrderId a/o typeId", Response::HTTP_NO_CONTENT);
        }

        if (!$locationId)
            $location = $purchaseOrder->getLocation();
        else {
            $location = $em->getReference(\AppBundle\Entity\Location::class, $locationId);
            if (!$location) $location = $purchaseOrder->getLocation();
        }
        
        if (!$sku) $sku = time();

        $product = new Product();
        $repo->generateProductAttributeRelations($product);
        $r = $repo->generateProductOrderRelation($product, $purchaseOrder, $quantity);
        $r->setPrice($purchasePrice);
        $product->setName($name);
        $product->setType($type);
        $product->setStatus($status);
        $product->setLocation($location);
        $product->setDescription($description);
        $product->setPrice($retailPrice);
        $product->setSku($sku);

        $em->persist($product);
        $em->flush();

        return new View("Product saved successfully", Response::HTTP_OK);
    }

        /**
     * @Rest\Put("/purchaseorderlocation")
     */
    public function updateLocationAction(Request $request)
    {
        $purchaseOrderId = $request->get('purchaseOrderId');
        $locationId = $request->get('locationId');

        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository(PurchaseOrder::class);

        /** @var PurchaseOrder */
        $order = $repo->findMineById($this->getUser(), $purchaseOrderId);

        if ($order === null)
        {
            return new View("No order found", Response::HTTP_NO_CONTENT);
        }

        $order->setLocation($em->getReference(\AppBundle\Entity\Location::class, $locationId));

        $em->flush();

        return new View("Order updated successfully", Response::HTTP_OK);
    }

    /**
     * @Rest\Put("/purchaseorderquantity")
     */
    public function updateQuantityAction(Request $request)
    {
        $purchaseOrderId = $request->get('purchaseOrderId');
        $productId = $request->get('productId');
        $quantity = $request->get('quantity');

        if (!$quantity || !is_numeric($quantity))
        {
            return new View("Quantity value is not valid", Response::HTTP_NO_CONTENT);
        }

        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository(PurchaseOrder::class);

        /** @var PurchaseOrder */
        $order = $repo->findMineById($this->getUser(), $purchaseOrderId);
     
        if ($order === null)
        {
            return new View("No order found", Response::HTTP_NO_CONTENT);
        }

        $r = $order->getProductRelation($productId);

        if ($r === null)
        {
            return new View("Product is not yet in order", Response::HTTP_NO_CONTENT);
        }

        $r->setQuantity($quantity);

        $em->flush();

        return new View("Order updated successfully", Response::HTTP_OK);
    }
}