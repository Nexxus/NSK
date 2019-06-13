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
 * Copiatek � info@copiatek.nl � Postbus 547 2501 CM Den Haag
 */

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class IndexBulkEditForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $index = $builder->getData();

        $builder
            ->setMethod('GET')
            ->add('index', ChoiceType::class, [
                'choices' => $index,
                'multiple' => true, 
                'expanded' => true, 
                'label' => false, 
                'required' => true])
            ->add('action', ChoiceType::class, [
                'choices' => [
                    'With selected lines...' => '',
                    'Edit status and location' => 'status',
                    'Print barcodes' => 'barcodes',
                    'Print price cards' => 'pricecards',
                    'Print checklists' => 'checklists'
                ],
                'choice_attr' => function($key, $val, $index) {
                    return !$key ? ['disabled' => 'disabled'] : [];
                },
                'multiple' => false, 
                'expanded' => false, 
                'label' => false, 
                'required' => false]);

        // To get the choices in the view: form.themes.vars.choices

        // Or with hardcoded HTML input: <input type="checkbox" id="index_bulk_edit_form_index_23" name="index_bulk_edit_form[index][]" value="23">
    }
}