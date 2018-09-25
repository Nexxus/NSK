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
 * Service
 *
 * @ORM\Entity
 */
class Service extends Product
{
    const STATUS_NEW = 0;
    const STATUS_TODO = 1;
    const STATUS_HOLD = 2;
    const STATUS_BUSY = 3;
    const STATUS_DONE = 4;

    /**
     * @var int Use constants
     *
     * @ORM\Column(type="integer", nullable=false, options={"default" : 0})
     */
    private $status = 0;

    /**
     * @var Product Product to which this Service is applied to
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="services", fetch="EAGER")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * @var Task Repair task that will be or is applied in this Service
     *
     * @ORM\ManyToOne(targetEntity="Task", inversedBy="services", fetch="EAGER")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=false)
     */
    private $task;

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Service
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set product
     *
     * @param Product $product
     *
     * @return ProductImage
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
     * Set task
     *
     * @param Task $task
     *
     * @return Service
     */
    public function setTask(Task $task)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get task
     *
     * @return Task
     */
    public function getTask()
    {
        return $this->task;
    }

}
