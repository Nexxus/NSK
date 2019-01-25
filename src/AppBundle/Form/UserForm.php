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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class UserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var User */
        $user = $builder->getData();

        $authCheck = $options['authCheck'];

        if (!$authCheck->isGranted('ROLE_SUPER_ADMIN') && $user->hasRole("ROLE_SUPER_ADMIN"))
        {
            throw new \Exception("Super admin cannot be edited by you!");
        }

        $builder
            ->add('username', TextType::class)
            ->add('firstname', TextType::class, ['required' => false])
            ->add('lastname', TextType::class, ['required' => false])
            ->add('email', EmailType::class);

        if ($authCheck->isGranted('ROLE_SUPER_ADMIN'))
        {
            $builder->add('plainPassword', RepeatedType::class, array(
                'required' => true, // see below
                'type' => PasswordType::class,
                'first_options'  => array('label' => 'New password'),
                'second_options' => array('label' => 'Repeat password'),
            ));

            if ($user->getId() > 0)
            {
                $builder->get("plainPassword")->setRequired(false);
            }
        }

        $builder
            ->add('locations',  EntityType::class, array(
                'class' => 'AppBundle:Location',
                'choice_label' => 'name',
                'attr' => ['class' => 'multiselect'],
                'multiple' => true,
                'expanded' => false,
                'query_builder' => function (EntityRepository $er) { return $er->createQueryBuilder('x')->orderBy("x.name", "ASC"); }
            ))
            ->add('role', ChoiceType::class, ['mapped' => false,
                'choices' => [
                    'Super_admin' => 'ROLE_SUPER_ADMIN',
                    'Admin' => 'ROLE_ADMIN',
                    'Manager' => 'ROLE_MANAGER',
                    'Local' => 'ROLE_LOCAL'
                ]])
            ->add('enabled', CheckboxType::class, ['required' => false])
            ->add('save', SubmitType::class, ['attr' => ['class' => 'btn-success btn-120']]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'user'
        ));

        $resolver->setRequired(array('authCheck'));
    }
}