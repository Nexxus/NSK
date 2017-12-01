<?php

namespace TrackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductAttribute
 * 
 * Attributes unique to specific products are stored here
 *
 * @ORM\Table(name="product_attribute")
 * @ORM\Entity(repositoryClass="TrackBundle\Repository\ProductAttributeRepository")
 */
class ProductAttribute
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="productid", type="integer")
     */
    private $productid;

    /**
     * @var int
     *
     * @ORM\Column(name="attr_id", type="integer")
     */
    private $attrId = 1;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=true)
     */
    private $value;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set productid
     *
     * @param integer $productid
     *
     * @return ProductAttribute
     */
    public function setProductid($productid)
    {
        $this->productid = $productid;

        return $this;
    }

    /**
     * Get productid
     *
     * @return int
     */
    public function getProductid()
    {
        return $this->productid;
    }

    /**
     * Set attrId
     *
     * @param integer $attrId
     *
     * @return ProductAttribute
     */
    public function setAttrId($attrId)
    {
        $this->attrId = $attrId;

        return $this;
    }

    /**
     * Get attrId
     *
     * @return int
     */
    public function getAttrId()
    {
        return $this->attrId;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return ProductAttribute
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
}

