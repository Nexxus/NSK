<?php

namespace TrackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductTypeAttribute
 * 
 * This determines which product types get 
 * which attributes by default
 *
 * @ORM\Table(name="product_type_attribute")
 * @ORM\Entity(repositoryClass="TrackBundle\Repository\ProductTypeAttributeRepository")
 */
class ProductTypeAttribute
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
     * @ORM\Column(name="type_id", type="integer")
     */
    private $typeId;

    /**
     * @var int
     * @ORM\ManyToOne(targetEntity="Attribute")
     * @ORM\JoinColumn(name="attr_id", referencedColumnName="id")
     */
    private $attrId = 1;

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
     * Set typeId
     *
     * @param integer $typeId
     *
     * @return ProductTypeAttribute
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * Get typeId
     *
     * @return int
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * Set attrId
     *
     * @param integer $attrId
     *
     * @return ProductTypeAttribute
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
}

