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

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use AppBundle\Helper\IndexSearchContainer;

class IndexSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var IndexSearchContainer */
        $container = $builder->getData();

        $builder->setMethod('GET')
            ->add('query', TextType::class, ['label' => false, 'required' => false, 'trim' => true, 'attr' => ['class' => 'focus']])
            ->add('submit', SubmitType::class, ['label' => 'Search']);

        if ($container === null || $container->className === null) // Dashboard
        {
            $builder->add('type', ChoiceType::class, [
                'label' => false,
                'expanded' => true,
                'multiple' => false,
                'data' => 'barcode',
                'choices' => [
                    'Barcodes' => 'barcode',
                    'Productnamen' => 'product',
                    'Inkooporders' => 'purchaseorder',
                    'Verkooporders' => 'salesorder',
                    'Klanten' => 'customer',
                    'Leveranciers' => 'supplier'
                ]]);
        }
        else
        {
            $builder->add('location',  EntityType::class, [
                'class' => 'AppBundle:Location',
                'choice_label' => 'name',
                'placeholder' => 'All locations',
                'required' => false,
                'query_builder' => function (EntityRepository $er) use ($container) { 
                    $qb = $er->createQueryBuilder('x')->orderBy("x.name", "ASC");
                    /** @var IndexSearchContainer $container */
                    if ($container->user->hasRole("ROLE_LOCAL") || $container->user->hasRole("ROLE_LOGISTICS"))
                        $qb = $qb->where('x.id IN (:locationIds)')->setParameter('locationIds', $container->user->getLocationIds()); 
                    return $qb;
                }
            ]);

            if ($container->className == \AppBundle\Entity\Product::class)
            {
                $builder->add('status',  EntityType::class, [
                    'class' => 'AppBundle:ProductStatus',
                    'choice_label' => 'name',
                    'placeholder' => 'All statuses',
                    'required' => false,
                    'query_builder' => function (EntityRepository $er) { return $er->createQueryBuilder('x')->orderBy("x.name", "ASC"); }
                ]);
    
                $builder->add('producttype',  EntityType::class, [
                    'class' => 'AppBundle:ProductType',
                    'choice_label' => 'name',
                    'placeholder' => 'All types',
                    'required' => false,
                    'query_builder' => function (EntityRepository $er) { return $er->createQueryBuilder('x')->orderBy("x.name", "ASC"); }
                ]);

                $builder->add('availability', ChoiceType::class, array(
                    'placeholder' => 'All availability',
                    'required' => false,
                    'choices' => [
                        'In stock' => 'InStock',
                        'On hold' => 'OnHold',
                        'For sale' => 'Saleable',
                        'Sold' => 'Sold'
                ]));
            }
            elseif ($container->className == \AppBundle\Entity\SalesOrder::class)
            {
                $builder->add('status', EntityType::class, [
                    'class' => 'AppBundle:OrderStatus',
                    'choice_label' => 'name',
                    'placeholder' => 'All statuses',
                    'required' => false,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('os')->where('os.isSale = true')->orderBy("os.name", "ASC");
                    }
                ]);
            }
            elseif ($container->className == \AppBundle\Entity\PurchaseOrder::class)
            {
                $builder->add('status', EntityType::class, [
                    'class' => 'AppBundle:OrderStatus',
                    'choice_label' => 'name',
                    'placeholder' => 'All statuses',
                    'required' => false,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('os')->where('os.isPurchase = true')->orderBy("os.name", "ASC");
                    }
                ]);
            }
        }
    }
}