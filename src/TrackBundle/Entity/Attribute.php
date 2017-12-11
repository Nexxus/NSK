<?php

namespace TrackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Attribute
 *
 * @ORM\Table(name="attribute")
 * @ORM\Entity(repositoryClass="TrackBundle\Repository\AttributeRepository")
 */
class Attribute
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
     * @ORM\Column(name="attr_code", type="string", length=11, unique=false)
     */
    private $attr_code;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @
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
     * Set code
     * 
     * @param string @code
     * 
     * @return Attribute
     */
    function setAttrCode($attr_code) {
        $this->attr_code = $attr_code;
        
        return $this;
    }
    
    /**
     * Get code
     * 
     * @return int
     */
    function getAttrCode() {
        return $this->attr_code;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Attribute
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

