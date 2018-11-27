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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\AService;

/**
 * ChecklistServiceForm is parented by ChecklistForm
 */
class ChecklistServiceForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*
         * Since this form type is used in a collection,
         * the object is not yet present at the buildForm function call.
         * Therefor the code can be wrapped in the presetdata event listener.
         */
		$builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $form = $event->getForm();

            /** @var AService */
            $service = $event->getData();

            $form
                ->add('status', ChoiceType::class, [
                    'attr' => ['class' => 'service-status'],
                    'choices' => [
                        'New' => AService::STATUS_NEW,
                        'Todo' => AService::STATUS_TODO,
                        'Hold' => AService::STATUS_HOLD,
                        'Busy' => AService::STATUS_BUSY,
                        'Done' => AService::STATUS_DONE
                    ]])
                ->add('done', CheckboxType::class, [
                    'mapped' => false,
                    'required' => false,
                    'data' => $service->getStatus() == AService::STATUS_DONE,
                    'attr' => ['class' => 'service-done']])
                ->add('description', TextareaType::class, ['required' => false]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AService::class,
        ));
    }
}
