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

namespace TrackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use TrackBundle\Entity\AOrder;
use AdminBundle\Entity\ACompany;
use AppBundle\Entity\User;
use TrackBundle\Entity\Product;

/**
 * Location
 *
 * Locations bound to products by ID
 *
 * @ORM\Table(name="location")
 * @ORM\Entity
 */
class Location
{
    public function __construct() {
        $this->products = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->companies = new ArrayCollection();
    }

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
     * @var ArrayCollection|Product[]
     *
     * @ORM\OneToMany(targetEntity="Product", mappedBy="location", fetch="LAZY")
     */
    private $products;

    /**
     * @var ArrayCollection|User[]
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\User", mappedBy="location", fetch="LAZY")
     */
    private $users;

    /**
     * @var ArrayCollection|AOrder[]
     *
     * @ORM\OneToMany(targetEntity="AOrder", mappedBy="location", fetch="LAZY")
     */
    private $orders;

    /**
     * @var ArrayCollection|ACompany[]
     *
     * @ORM\OneToMany(targetEntity="AdminBundle\Entity\ACompany", mappedBy="location", fetch="LAZY")
     */
    private $companies;

    /**
     * Get id
     *
     * @return integer
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
     * @return Location
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
     * Add product
     *
     * @param Product $product
     *
     * @return Location
     */
    public function addProduct(Product $product)
    {
        $this->products[] = $product;

        return $this;
    }

    /**
     * Remove product
     *
     * @param Product $product
     */
    public function removeProduct(Product $product)
    {
        $this->products->removeElement($product);
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Add order
     *
     * @param AOrder $order
     *
     * @return Location
     */
    public function addOrder(AOrder $order)
    {
        $this->orders[] = $order;

        return $this;
    }

    /**
     * Remove order
     *
     * @param AOrder $order
     */
    public function removeOrder(AOrder $order)
    {
        $this->orders->removeElement($order);
    }

    /**
     * Get orders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * Add company
     *
     * @param ACompany $company
     *
     * @return Location
     */
    public function addCompany(ACompany $company)
    {
        $this->companies[] = $company;

        return $this;
    }

    /**
     * Remove company
     *
     * @param ACompany $company
     */
    public function removeCompany(ACompany $company)
    {
        $this->companies->removeElement($company);
    }

    /**
     * Get companies
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCompanies()
    {
        return $this->companies;
    }

    /**
     * Add user
     *
     * @param User $user
     *
     * @return Location
     */
    public function addUser(User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param User $user
     */
    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    public function __toString() {
        return $this->getName();
    }
}
