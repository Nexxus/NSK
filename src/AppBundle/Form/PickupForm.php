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
use AppBundle\Entity\Pickup;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PickupForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('supplier', SupplierForm::class, [
                'property_path' => 'order.supplier'
            ])
            ->add('pickupDate', DateType::class, ['required' => false])
            ->add('dataDestruction', ChoiceType::class, [
                'expanded' => false,
                'multiple' => false,
                'choices' => [
                    'Format is voldoende (gratis)' => Pickup::DATADESTRUCTION_FORMAT,
                    'Geen HDD aangeleverd' =>  Pickup::DATADESTRUCTION_NONE,
                    'Vernietigingsverklaring (gratis)' => Pickup::DATADESTRUCTION_STATEMENT,
                    'HDD op locatie shredden a €12,50 (extra 0.89ct per KM)' => Pickup::DATADESTRUCTION_SHRED,
                    'HDD wipe report KillDisk a €3,50' => Pickup::DATADESTRUCTION_KILLDISK
                ]
            ])
            ->add('description', IntegerType::class, ['required' => false])
            ->add('qComputer', IntegerType::class, ['mapped' => false, 'required' => false])
            ->add('qServer', IntegerType::class, ['mapped' => false, 'required' => false])
            ->add('qPhone', IntegerType::class, ['mapped' => false, 'required' => false])
            ->add('qPrinter', IntegerType::class, ['mapped' => false, 'required' => false])
            ->add('qMonitor', IntegerType::class, ['mapped' => false, 'required' => false])
            ->add('images-input', FileType::class, ['mapped' => false, 'required' => false])
            ->add('agreement-input', FileType::class, ['mapped' => false, 'required' => false])
            ->add('images-names', HiddenType::class, ['mapped' => false, 'required' => false])
            ->add('agreement-names', HiddenType::class, ['mapped' => false, 'required' => false])
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
            'data_class' => Pickup::class,
            'csrf_protection' => false
        ));
    }
}