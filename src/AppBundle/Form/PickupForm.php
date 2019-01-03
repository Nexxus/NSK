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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class PickupForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


            // First the mapped fields
           $builder
                ->add('supplier', SupplierForm::class, ['property_path' => 'order.supplier'])
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
                ->add('description', TextareaType::class, ['required' => false]);

            // Then the unmapped fields for quantities
            foreach ($options['productTypes'] as $productType)
            {
                $builder->add('q' . $productType->getName(), IntegerType::class, [
                    'mapped' => false,
                    'required' => false,
                    'label' => $productType->getName() . " aantal",
                    'attr' => ['placeholder' => '0']]);
            }

            // Then the unmapped fields for files
            $builder
                ->add('imagesInput', FileType::class, ['mapped' => false, 'required' => false, 'label' => 'Images'])
                ->add('agreementInput', FileType::class, ['mapped' => false, 'required' => false, 'label' => 'Processing Agreement']);

            // Finally the hidden fields
            $builder
                ->add('imagesNames', HiddenType::class, ['mapped' => false, 'required' => false])
                ->add('agreementName', HiddenType::class, ['mapped' => false, 'required' => false])
                ->add('orderStatusName', HiddenType::class, ['mapped' => false, 'required' => true, 'data' => "To plan and pickup"])
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
            'data_class' => Pickup::class,
            'csrf_protection' => false
        ));

        $resolver->setRequired(array('productTypes'));
    }
}
