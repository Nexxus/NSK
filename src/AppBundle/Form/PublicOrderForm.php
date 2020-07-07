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
use AppBundle\Entity\Customer;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class PublicOrderForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            // First the mapped fields
            ->add('customer', PublicCompanyForm::class, ['data_class' => Customer::class]);

            // Then the unmapped fields: products
            $builder->add('products', CollectionType::class, [
                'entry_type' => PublicOrderProductForm::class,
                'mapped' => false,
                'allow_add' => true,
                'data' => $options['products']
            ]);

            // Finally the hidden fields
            $builder
            ->add('orderStatusName', HiddenType::class, ['mapped' => false, 'required' => true, 'data' => $options['orderStatusName']])
            ->add('locationId', HiddenType::class, ['mapped' => false, 'required' => true, 'data' => $options['locationId']])
            ->add('confirmPage', HiddenType::class, ['mapped' => false, 'required' => false, 'data' => $options['confirmPage']])
            ->add('save', SubmitType::class, [
                'label' => 'Send',
                'attr' => [
                    'class' => 'btn-success',
                    'style' => 'width: 150px;',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => SalesOrder::class,
            'csrf_protection' => false,
            'products' => null,
        ));

        $resolver->setRequired(array('orderStatusName', 'locationId', 'confirmPage'));
    }
}