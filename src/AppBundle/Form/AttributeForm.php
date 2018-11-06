<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Attribute;
use AppBundle\Entity\ProductType;
use AppBundle\Helper\AttributeOptionTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AttributeForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Attribute */
        $attribute = $builder->getData();

        $builder
            ->add('attr_code')
            ->add('name')
            ->add('productTypes', EntityType::class, array(
                    'label' => 'Products',
                    'required' => false,
                    'multiple' => true,
                    'expanded' => false,
                    'class' => ProductType::class,
                    'choice_label' => 'name',
                    'attr' => ['class' => 'multiselect']));

        if (!$attribute->getId())
        {
            $builder->add('type', ChoiceType::class, array(
                    'choices' => [
                        'Text' => $attribute::TYPE_TEXT,
                        'Select' => $attribute::TYPE_SELECT,
                        'File' => $attribute::TYPE_FILE,
                        'Product' => $attribute::TYPE_PRODUCT
                ]));
        }
        elseif ($attribute->getType() == Attribute::TYPE_SELECT)
        {
            $builder->add('options', CollectionType::class, [
                'entry_type' => AttributeOptionForm::class,
                'entry_options' => ['label' => false]
            ]);
        }
        elseif ($attribute->getType() != Attribute::TYPE_PRODUCT)
        {
            $builder->add('price', MoneyType::class);
        }

        $builder->add('save', SubmitType::class, ['attr' => ['class' => 'btn-success btn-120']]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Attribute::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'attribute',
        ));
    }
}