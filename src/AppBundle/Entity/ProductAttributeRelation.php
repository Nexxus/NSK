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

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="product_attribute")
 * @ORM\Entity
 */
class ProductAttributeRelation
{
    /**
     * @var string Text, File path or Option text; depends on type of attribute
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $value;

    /**
     * @var Product If attribute type is product, this field contains a product which has property isAttribute=true and that is/becomes attribute of containing product.
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="attributedRelations")
     * @ORM\JoinColumn(name="value_product_id", referencedColumnName="id", nullable=true)
     */
    private $valueProduct;

    /**
     * @var Product This is the product containing the attribute
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="attributeRelations", fetch="EAGER")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false)
     */
    private $product;

    /**
     * @var Attribute
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Attribute", inversedBy="productRelations", fetch="EAGER")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", nullable=false)
     */
    private $attribute;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantity;

    #region db getters and setters

    /**
     * Set value
     *
     * @param string $value
     *
     * @return ProductAttributeRelation
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set valueProduct
     *
     * @param Product $valueProduct
     *
     * @return ProductAttributeRelation
     */
    public function setValueProduct(Product $valueProduct = null)
    {
        $this->valueProduct = $valueProduct;

        return $this;
    }

    /**
     * Get valueProduct
     *
     * @return Product
     */
    public function getValueProduct()
    {
        return $this->valueProduct;
    }

    /**
     * Set product
     *
     * @param Product $product
     *
     * @return ProductAttributeRelation
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set attribute
     *
     * @param Attribute $attribute
     *
     * @return ProductAttributeRelation
     */
    public function setAttribute(Attribute $attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get attribute
     *
     * @return Attribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    public function __toString(){
        return $this->getValue();
    }

    /**
     * Get files of product and attribute
     *
     * @return \Doctrine\Common\Collections\Collection|ProductAttributeFile[]
     */
    public function getFiles()
    {
        if ($this->attribute->getType() != Attribute::TYPE_FILE)
            throw new \Exception("Attribute must be of type File.");

        $fileIds = explode(",", $this->value);
        $fileIds = array_map('intval', $fileIds);

        return $this->getProduct()->getFiles()
            ->filter(function ($file) use ($fileIds) {
                         return in_array($file->getId(), $fileIds);
                     });
    }

    /**
     * Set quantity of attributed products (issue #87)
     *
     * @param integer $quantity
     *
     * @return ProductAttributeRelation
     */
    public function setQuantity($quantity)
    {
        if ($this->getAttribute()->getType() != Attribute::TYPE_PRODUCT)
            throw new \Exception("Quantity can only be set when it is an attributed product");

        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity of attributed products (issue #87)
     *
     * @return integer
     */
    public function getQuantity()
    {
        if ($this->getAttribute()->getType() != Attribute::TYPE_PRODUCT || !$this->quantity)
            return 1;

        return $this->quantity;
    }

    #endregion

    /**
     * @return AttributeOption
     */
    public function getSelectedOption()
    {
        if ($this->getAttribute()->getType() != Attribute::TYPE_SELECT)
            throw new \Exception("The attribute is not selectable.");

        return $this->getAttribute()->getOptions()->filter(
            function (AttributeOption $o)  {
                $o->getName() == $this->getValue();
            })->first();
    }

    /**
     * Standard price multiplied by Quantity of (selected) attribute or attributed product
     * @return double
     */
    public function getTotalStandardPrice()
    {
        $price = 0;

        switch ($this->getAttribute()->getType())
        {
            case Attribute::TYPE_SELECT:
                $option = $this->getSelectedOption();
                $price = $option ? $option->getPrice() : 0;
                break;
            case Attribute::TYPE_PRODUCT:
                $product = $this->getValueProduct();
                $price = $product ? $product->getPrice() * $this->getQuantity() : 0;
                break;
            default:
                $price = $this->getAttribute()->getPrice();
                break;
        }

        return $price;
    }
}
