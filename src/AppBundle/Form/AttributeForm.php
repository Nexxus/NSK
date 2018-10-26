<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Attribute;
use AppBundle\Helper\AttributeOptionTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AttributeForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Attribute */
        $attribute = $builder->getData();

        $builder->add('attr_code')->add('name')->add('productTypes')->add('type', ChoiceType::class, [
                    'choices' => [
                        'Text' => $attribute::TYPE_TEXT,
                        'Selectbox' => $attribute::TYPE_SELECT,
                        'File' => $attribute::TYPE_FILE,
                        'Product' => $attribute::TYPE_PRODUCT
                    ]
                ]);

        if ($attribute->getType() == Attribute::TYPE_SELECT)
        {
            $builder->add('options', TextType::class, array(
                        'required' => false, 'label' => 'Options (comma sep)'));

            $builder->get('options')->addModelTransformer(new AttributeOptionTransformer($attribute));
        }
        else if ($attribute->getType() != Attribute::TYPE_PRODUCT)
        {
            $builder->add('price');
        }

    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Attribute'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'trackbundle_attribute';
    }


}