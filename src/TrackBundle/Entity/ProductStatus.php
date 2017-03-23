<?php

namespace TrackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductStatus
 *
 * @ORM\Table(name="product_status")
 * @ORM\Entity(repositoryClass="TrackBundle\Repository\ProductStatusRepository")
 */
class ProductStatus
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
     * @ORM\Column(name="pindex", type="integer", nullable=true)
     */
    private $pindex;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


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
     * Set pindex
     *
     * @param integer $pindex
     *
     * @return ProductStatus
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
     * Set name
     *
     * @param string $name
     *
     * @return ProductStatus
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
     * If object is treated like a string: return name
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}

