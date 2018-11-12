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

namespace AppBundle\Helper;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;
use AppBundle\Entity\AttributeOption;
use AppBundle\Entity\Attribute;

class AttributeOptionTransformer implements DataTransformerInterface
{
    private $attribute;

    function __construct(Attribute $attribute) {
        $this->attribute = $attribute;
    }

    public function transform($optionCollection)
    {
        $options = array();

        foreach ($optionCollection as $option)
        {
            $options[] = $option->getName();
        }

        return implode(',', $options);
    }

    public function reverseTransform($options)
    {
        $optionNames = explode(',', $options);
        $optionNames = array_map('trim', $optionNames);

        // add
        foreach ($optionNames as $optionName)
        {
            $exists = $this->attribute->getOptions()->exists(function($key, $option) use ($optionName) { return $option->getName() == $optionName; });

            if (!$exists)
            {
                $option = new AttributeOption();
                $option->setName($optionName);
                $option->setAttribute($this->attribute);
                $this->attribute->addOption($option);
            }
        }

        // remove
        foreach ($this->attribute->getOptions() as $option)
        {
            if (!in_array($option->getName(), $optionNames))
            {
                $this->attribute->removeOption($option);
            }
        }

        return $this->attribute->getOptions();
    }

}