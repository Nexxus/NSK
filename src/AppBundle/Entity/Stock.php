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
 * Copiatek - info@copiatek.nl - Postbus 547 2501 CM Den Haag
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serialize;

/**
 * This entity is a database view; therefor you see readOnly annotation and a private constructor
 * Create View statements at bottom of this file
 * See also class Helper/IgnoreTablesListener
 * Source: https://stackoverflow.com/questions/12563005/ignore-a-doctrine2-entity-when-running-schema-manager-update/25948910#25948910
 *
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="stock")
 */
class Stock
{
    private function __construct() {}

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    private $id;   
    
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Serialize\Groups({"vue:products"})
     */
    private $purchased;     

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Serialize\Groups({"vue:products"})
     */
    private $stock;  

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Serialize\Groups({"vue:products"})
     */
    private $saleable;  
    
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Serialize\Groups({"vue:products"})
     */
    private $hold;  
    
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @Serialize\Groups({"vue:products"})
     */
    private $sold; 
    
    /**
     *
     * @var Product
     * @ORM\OneToOne(targetEntity="Product", inversedBy="stock")
     * @ORM\JoinColumn(name="id", referencedColumnName="id")
     */
    private $product;    

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getProduct()
    {
        return $this->product;
    }    

    /**
     * @return integer
     */
    public function getPurchased()
    {
        return $this->purchased;
    }

    /**
     * @return integer
     */
    public function getStock()
    {
        return $this->stock;
    }
    
    /**
     * @return integer
     */
    public function getSaleable()
    {
        return $this->saleable;
    }
    
    /**
     * @return integer
     */
    public function getHold()
    {
        return $this->hold;
    }
    
    /**
     * @return integer
     */
    public function getSold()
    {
        return $this->sold;
    }    
}

/*

CREATE VIEW stock_sub AS
SELECT 
p.id,
coalesce(p_o.quantity, 0) as purchased,
0 as stock,
0 as sale,
0 as sold,
0 as sold_attributed
FROM product p
LEFT JOIN product_order p_o ON p.id=p_o.product_id AND p_o.order_id IN (SELECT id FROM aorder WHERE discr='p')
UNION
SELECT 
p.id,
0 as purchased,
sum(p_o.quantity) as stock,
0 as sale,
0 as sold,
0 as sold_attributed 
FROM product p
LEFT JOIN product_status s ON p.status_id=s.id
JOIN product_order p_o ON p.id=p_o.product_id AND p_o.order_id IN (SELECT id FROM aorder WHERE discr='p' OR (discr='s' AND id in (select order_id from `repair`)))
WHERE s.is_stock IS NULL
GROUP BY p.id
UNION
SELECT 
p.id,
0 as purchased,
0 as stock,
p_o.quantity as sale,
0 as sold,
0 as sold_attributed 
FROM product p
JOIN product_status s ON p.status_id=s.id and s.is_saleable IS NULL
JOIN product_order p_o ON p.id=p_o.product_id AND p_o.order_id IN (SELECT id FROM aorder WHERE discr='p')
UNION
SELECT 
p.id,
0 as purchased,
0 as stock,
0 as sale,
sum(coalesce(p_o.quantity, 0)) as sold,
0 as sold_attributed 
FROM product p
JOIN product_status s ON p.status_id=s.id and s.is_saleable IS NULL
JOIN product_order p_o ON p.id=p_o.product_id AND p_o.order_id IN (SELECT id FROM aorder WHERE discr='s')
group by p.id
UNION
SELECT 
p.id,
0 as purchased,
0 as stock,
0 as sale,
0 as sold,
sum(coalesce(p_a.quantity, 0)*coalesce(p_o.quantity, 0)) as sold_attributed 
FROM product p
JOIN product_attribute p_a ON p.id=p_a.value_product_id
JOIN product p2 ON p2.id=p_a.product_id
JOIN product_status s ON p2.status_id=s.id and s.is_saleable IS NULL
JOIN product_order p_o ON p2.id=p_o.product_id AND p_o.order_id IN (SELECT id FROM aorder WHERE discr='s')
group by p.id
;
CREATE VIEW stock AS
SELECT
id,
SUM(purchased) as purchased,
sum(stock)-sum(sold) as stock,
GREATEST(SUM(sale) -SUM(sold), 0) as saleable,
GREATEST(SUM(stock)-SUM(sale), 0) as hold,
SUM(sold)+sum(sold_attributed) as sold
FROM stock_sub
GROUP BY id

*/