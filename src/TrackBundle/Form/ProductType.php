<?php

namespace TrackBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('sku', TextType::class)
                ->add('name', TextType::class)
                ->add('location',  EntityType::class, array(
                    'class' => 'TrackBundle:Location',
                    'choice_label' => 'name'
                ))
                ->add('type',  EntityType::class, array(
                    'class' => 'TrackBundle:ProductType',
                    'choice_label' => 'name'
                ))
                ->add('description', TextType::class)
                ->add('status')
                ->add('brand', TextType::class)
                ->add('department')
                ->add('owner')
                ->add('quantity');
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TrackBundle\Entity\Product'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'trackbundle_product';
    }


}
