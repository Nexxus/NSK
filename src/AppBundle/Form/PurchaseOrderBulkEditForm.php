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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\PurchaseOrder;

class PurchaseOrderBulkEditForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['user'];
        $orders = $builder->getData();
        
        $builder
            ->add('orders', EntityType::class, [
                'class' => PurchaseOrder::class,
                'choice_label' => 'orderNr',
                'choices' => $orders,
                'data' => $orders,
                'multiple' => true, 
                'expanded' => false, 
                'attr' => ['class' => 'multiselect'],
                'required' => true])
            ->add('status',  EntityType::class, [
                'class' => 'AppBundle:OrderStatus',
                'choice_label' => 'name',
                'required' => false,
                'query_builder' => function (EntityRepository $er) { return $er->createQueryBuilder('x')->orderBy("x.name", "ASC"); }
            ])
            ->add('location',  EntityType::class, [
                'class' => 'AppBundle:Location',
                'choice_label' => 'name',
                'required' => false,
                'query_builder' => function (EntityRepository $er) use ($user) { 
                    $qb = $er->createQueryBuilder('x')->orderBy("x.name", "ASC");
                    /** @var \AppBundle\Entity\User $user */
                    if ($user->hasRole("ROLE_LOCAL") || $user->hasRole("ROLE_LOGISTICS"))
                        $qb = $qb->where('x.id IN (:locationIds)')->setParameter('locationIds', $user->getLocationIds()); 
                    return $qb;
                }
            ])            
            ->add('save', SubmitType::class, [
                'attr' => ['class' => 'btn-success btn-120']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'pobulkedit',
        ));

        $resolver->setRequired(array('user'));
    }
}
