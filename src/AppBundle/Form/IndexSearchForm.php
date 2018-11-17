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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class IndexSearchForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setMethod('GET')
            ->add('query', TextType::class, ['label' => false, 'required' => false, 'trim' => true, 'attr' => ['class' => 'focus']])
            ->add('submit', SubmitType::class, ['label' => 'Search']);

        if ($options['withRadioButtons'])
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
                    //'Locaties' => 'location'
                ]]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            //'allow_extra_fields' => true, // when request comes from dashboard
            'withRadioButtons' => false,
            'csrf_protection' => false
        ));
    }
}