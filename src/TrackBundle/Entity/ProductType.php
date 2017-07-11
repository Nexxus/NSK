<?php

namespace TrackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductType
 *
 * @ORM\Table(name="product_type")
 * @ORM\Entity(repositoryClass="TrackBundle\Repository\ProductTypeRepository")
 */
class ProductType
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="pindex", type="integer", nullable=true)
     */
    private $pindex;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;


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
     * Set name
     *
     * @param string $name
     *
     * @return ProductType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set pindex
     *
     * @param integer $pindex
     *
     * @return ProductType
     */
    public function setPindex($pindex)
    {
        $this->pindex = $pindex;

        return $this;
    }

    /**
     * Get pindex
     *
     * @return int
     */
    public function getPindex()
    {
        return $this->pindex;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return ProductType
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
    
    /**
     * If object is treated like a string: return name
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
