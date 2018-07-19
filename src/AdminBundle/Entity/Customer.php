<?php

namespace AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Customer
 *
 * @ORM\Table(name="customer")
 * @ORM\Entity(repositoryClass="AdminBundle\Repository\CustomerRepository")
 */
class Customer
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
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="sirname", type="string", length=255, nullable=true)
     */
    private $sirname;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var bool
     *
     * @ORM\Column(name="partner", type="boolean")
     */
    private $partner;

    /**
     * @var int
     * @ORM\ManyToOne(targetEntity="Company")
     * @ORM\JoinColumn(name="company", referencedColumnName="id", nullable=true)
     */
    private $companyId;

    /**
     * @var int
     * @ORM\ManyToOne(targetEntity="Address")
     * @ORM\JoinColumn(name="address", referencedColumnName="id", nullable=true)
     */
    private $addressId;


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
     * Set firstname
     *
     * @param string $firstname
     *
     * @return Customer
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set sirname
     *
     * @param string $sirname
     *
     * @return Customer
     */
    public function setSirname($sirname)
    {
        $this->sirname = $sirname;

        return $this;
    }

    /**
     * Get sirname
     *
     * @return string
     */
    public function getSirname()
    {
        return $this->sirname;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Customer
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Customer
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set partner
     *
     * @param boolean $partner
     *
     * @return Customer
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     * Get partner
     *
     * @return bool
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * Set companyId
     *
     * @param integer $companyId
     *
     * @return Customer
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;

        return $this;
    }

    /**
     * Get companyId
     *
     * @return int
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * Set addressId
     *
     * @param integer $addressId
     *
     * @return Company
     */
    public function setAddressId($addressId)
    {
        $this->addressId = $addressId;

        return $this;
    }

    /**
     * Get addressId
     *
     * @return int
     */
    public function getAddressId()
    {
        return $this->addressId;
    }
}

