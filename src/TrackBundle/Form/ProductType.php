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

namespace TrackBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('sku', TextType::class)
                ->add('name', TextType::class)
                ->add('quantity', IntegerType::class, array(
                    'required' => false
                ))
                ->add('location',  EntityType::class, array(
                    'class' => 'TrackBundle:Location',
                    'choice_label' => 'name'
                ))
                ->add('type',  EntityType::class, array(
                    'class' => 'TrackBundle:ProductType',
                    'choice_label' => 'name'
                ))
                ->add('description', TextType::class, array(
                    'required' => false
                ))
                ->add('status')
                ->add('brand', TextType::class, array(
                    'required' => false
                ))
                ->add('department', TextType::class, array(
                    'required' => false
                ))
                ->add('owner', TextType::class, array(
                    'required' => false
                ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TrackBundle\Entity\Product'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'trackbundle_product';
    }


}
