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
 * TaskService is a task that has been applied to a product just after it is purchased,
 * mostly to make it saleable. A task is an internal affair, not paid by any customer.
 *
 * @ORM\Entity
 */
class TaskService extends AService
{
    public function __construct(Task $task, ProductOrderRelation $productOrderRelation)
    {
        $order = $productOrderRelation->getOrder();
        $orderClass = get_class($order);

        if ($order == null || $orderClass != PurchaseOrder::class)
            throw new \Exception("Task service can only be bound to purchase order");

        $this->task = $task;

        parent::__construct($productOrderRelation);
    }

    /**
     * @var Task Task that will be or is applied in this Service
     *
     * @ORM\ManyToOne(targetEntity="Task", fetch="EAGER")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=true)
     */
    private $task;

    /**
     * Get task
     *
     * @return Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @return PurchaseOrder
     */
    public function getPurchaseOrder() {
        return $this->productOrderRelation->getOrder();
    }
}
