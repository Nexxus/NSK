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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use AppBundle\Entity\ProductAttributeRelation;
use AppBundle\Entity\Attribute;
use AppBundle\Entity\AttributeOption;

class ProductAttributeRelationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*
         * Since this form type is used in a collection,
         * the object is not yet present at the buildForm function call.
         * Therefor the code can be wrapped in the presetdata event listener.
         */
		$builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $form = $event->getForm();

            /** @var ProductAttributeRelation */
            $relation = $event->getData();

            // loading proper attribute type
            if ($relation->getAttribute())
            switch ($relation->getAttribute()->getType()) {
                case Attribute::TYPE_FILE:
                    $form->add('valueFiles', FileType::class, ['mapped' => false, 'required' => false, 'label' => 'Attachment'])
                         ->add('value', HiddenType::class, ['mapped' => false, 'required' => false]);
                    break;
                case Attribute::TYPE_PRODUCT:
                    $form->add('valueProduct', EntityType::class, [
                        'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                            return $er->createQueryBuilder('p')
                                ->join("p.type", "t")
                                ->where('t.isAttribute = true')
                                ->orderBy('p.sku', 'ASC');
                        },
                       'class' => 'AppBundle:Product',
                       'choice_label' => 'name',
                       'required' => false,
                       'label' => 'Part'
                    ]);
                    break;
                case Attribute::TYPE_SELECT:
                    $form->add('value', ChoiceType::class, [
                       'choices' => $this->getAttributeOptions($relation->getAttribute()),
                       'required' => false,
                       'label' => 'Select'
                    ]);
                    break;
                case Attribute::TYPE_TEXT:
                    $form->add('value', TextType::class, [
                        'label' => 'Description',
                        'required' => false,
                    ]);
                    break;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ProductAttributeRelation::class
        ));
    }

    private function getAttributeOptions(Attribute $attribute)
    {
        $options = $attribute->getOptions();
        $arr = array();

        foreach ($options as $option)
        {
            /** @var AttributeOption $option */
            $arr[$option->getName()] = $option->getId();
        }

        return $arr;
    }
}
