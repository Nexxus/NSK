<?php

namespace TrackBundle\Form;

use Symfony\Component\Form\AbstractType;
<<<<<<< HEAD
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
=======
>>>>>>> 41d639d70c9242217f0f548c91165216c37d1f60
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
<<<<<<< HEAD
        $builder->add('sku', TextType::class)
                ->add('name', TextType::class)
                ->add('location',  EntityType::class, array(
                    'class' => 'TrackBundle:Location',
                    'choice_label' => 'name'
                ))
                ->add('description', TextType::class)
                ->add('status')
                ->add('brand', TextType::class)
                ->add('department')
                ->add('owner');
=======
        $builder->add('sku')->add('groupSku')->add('name')->add('location')->add('description')->add('status')->add('brand')->add('department')->add('owner')        ;
>>>>>>> 41d639d70c9242217f0f548c91165216c37d1f60
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
