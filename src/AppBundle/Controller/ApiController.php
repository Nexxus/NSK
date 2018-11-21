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

        $repo->generateProductAttributeRelations($product);

        if ($product === null)
        {
            return new View("No product found", Response::HTTP_NO_CONTENT);
        }

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

        $attrfile = new ProductAttributeFile();
        $attrfile->setUniqueServerFilename($serverFilename);
        $attrfile->setOriginalClientFilename($file->getClientOriginalName());
        $attrfile->setProduct($product);
        $em->persist($attrfile);
        $em->flush($attrfile);

        $val = $relation->getValue() ? $relation->getValue() . "," . $attrfile->getId() : $attrfile->getId();
        $relation->setValue($val);

        $em->persist($relation);
        $em->flush();

        return new View("Attachment saved successfully", Response::HTTP_OK);
    }
}