<?php

namespace AdminBundle\Form;

use AdminBundle\Entity\Company;
use AdminBundle\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->
        add('firstname')->add('sirname')->add('phone')->add('email')->add('partner')->
        add('companyId', EntityType::class, array(
        'class' => 'AdminBundle:Company',
        'choice_label' => 'name'))->
        add('addressId', EntityType::class, array(
        'class' => 'AdminBundle:Address',
        'choice_label' => 'street1',));
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AdminBundle\Entity\Customer'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'adminbundle_customer';
    }


}
