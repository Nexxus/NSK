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
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use AppBundle\Entity\Product;
use AppBundle\Entity\ProductAttributeRelation;

class ProductForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sku', TextType::class, [
                'required' => false,
                'attr'=> ['placeholder' => 'Keep empty for autogeneration', 'class' => 'focus']
            ])
            ->add('name', TextType::class)
            ->add('name', TextType::class)
            ->add('price', MoneyType::class, [
                'required' => false,
                'label' => 'Standard'
            ])
            ->add('description', TextType::class, [
                'required' => false
            ])
            ->add('type',  EntityType::class, [
                'class' => 'AppBundle:ProductType',
                'choice_label' => 'name',
            ])
            ->add('status',  EntityType::class, [
                'class' => 'AppBundle:ProductStatus',
                'choice_label' => 'name',
                'required' => false
            ])
            ->add('attributeRelations', CollectionType::class, [
                'entry_type' => ProductAttributeRelationForm::class,
                'entry_options' => ['label' => false],
                'label' => 'Attributes',
            ])
            ->add('save', SubmitType::class, [
                'attr' => ['class' => 'btn-success btn-120']
            ]);

        /** @var \AppBundle\Entity\User */
        $user = $options['user'];

        if ($user && !$user->hasRole("ROLE_LOCAL"))
        {
            $builder->add('location',  EntityType::class, [
                    'class' => 'AppBundle:Location',
                    'choice_label' => 'name',
                    'required' => true
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Product::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'customer',
        ));

        $resolver->setRequired(array('user'));
    }
}
