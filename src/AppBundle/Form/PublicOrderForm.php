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

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use AppBundle\Entity\SalesOrder;

class PublicOrderForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            // First the mapped fields
            ->add('customer', CustomerForm::class)

            // Then the unmapped fields: quantities and files
            ->add('qComputer', IntegerType::class, ['mapped' => false, 'required' => false, 'label' => 'Computers'])
            ->add('qLaptop', IntegerType::class, ['mapped' => false, 'required' => false, 'label' => 'Laptops'])
            ->add('qLaptopElitebook820', IntegerType::class, ['mapped' => false, 'required' => false, 'label' => 'Laptops HP Elitebook 820'])

            // Finally the hidden fields
            ->add('orderStatusName', HiddenType::class, ['mapped' => false, 'required' => true, 'data' => "Products to assign"])
            ->add('locationId', HiddenType::class, ['mapped' => false, 'required' => true, 'data' => 1])
            ->add('save', SubmitType::class, [
                'label' => 'Send',
                'attr' => [
                    'class' => 'btn-success',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => SalesOrder::class,
            'csrf_protection' => false
        ));
    }
}