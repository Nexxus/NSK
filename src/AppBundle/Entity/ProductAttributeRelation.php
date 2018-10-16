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
     * @ORM\ManyToOne(targetEntity="Product")
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
}
