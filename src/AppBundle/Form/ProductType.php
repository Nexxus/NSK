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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;

use AppBundle\Entity\Product;

class ProductType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Product */
        $product = $builder->getData();

        $builder
            ->add('sku', TextType::class)
            ->add('name', TextType::class)
            ->add('quantity', IntegerType::class, [
                'required' => false
            ])
            ->add('price', IntegerType::class, [
                'required' => false
            ])
            ->add('description', TextType::class, [
                'required' => false
            ])
            ->add('type',  EntityType::class, [
                'class' => 'AppBundle:ProductType',
                'choice_label' => 'name',
                'required' => false,
            ])
            ->add('location',  EntityType::class, [
                'class' => 'AppBundle:Location',
                'choice_label' => 'name'
            ])
            ->add('status',  EntityType::class, [
                'class' => 'AppBundle:ProductStatus',
                'choice_label' => 'name'
            ])
            ->add('attributeRelations', CollectionType::class, [
                'entry_type' => ProductAttributeRelationType::class
            ])
            ->add('newAttribute',  EntityType::class, [
                'mapped' => false,
                'class' => 'AppBundle:Attribute',
                'choice_label' => 'name',
                'required' => false,
                'query_builder' => function (EntityRepository $er) use ($product) {
                    /** @var Product $product */
                    return $er->createQueryBuilder('a');
                }
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save Changes',
                'attr' => [
                    'class' => 'btn btn-success',
                ]
            ]);
    }
}
