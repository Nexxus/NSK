<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\ProductType;
use AppBundle\Entity\Attribute;
use AppBundle\Entity\Task;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class ProductTypeForm extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var ProductType */
        $productType = $builder->getData();

        $builder
            ->add('name', TextType::class, array('required' => true))
            ->add('comment', TextType::class, array('required' => false))
            ->add('pindex', IntegerType::class, array('required' => false))
            ->add('tasks', EntityType::class, array(
                    'label' => 'Possible tasks',
                    'required' => false,
                    'multiple' => true,
                    'expanded' => false,
                    'class' => Task::class,
                    'choice_label' => 'name',
                    'attr' => ['class' => 'multiselect'],
                    'query_builder' => function (EntityRepository $er) { return $er->createQueryBuilder('x')->orderBy("x.name", "ASC"); }
                    ))
            ->add('attributes', EntityType::class, array(
                    'required' => false,
                    'multiple' => true,
                    'expanded' => false,
                    'class' => Attribute::class,
                    'choice_label' => 'name',
                    'attr' => ['class' => 'multiselect'],
                    'query_builder' => function (EntityRepository $er) { return $er->createQueryBuilder('x')->orderBy("x.name", "ASC"); }
                    ))
            ->add('newTask', TextType::class, array('required' => false, 'mapped' => false, 'attr' => ['placeholder' => 'Add new task']))
            ->add('newAttribute', TextType::class, array('required' => false, 'mapped' => false, 'attr' => ['placeholder' => 'Add new attribute']))
            ->add('newAttributeType', ChoiceType::class, array('required' => false, 'mapped' => false,
                    'choices' => [
                        'Text' => Attribute::TYPE_TEXT,
                        'Select' => Attribute::TYPE_SELECT,
                        'File' => Attribute::TYPE_FILE,
                        'Product' => Attribute::TYPE_PRODUCT
                ]))
            ->add('isAttribute', CheckboxType::class, array('required' => false, 'label' => '"' . $productType->getName() . '" can be a part. If checked, products of this type can be an attribute of other product.'))
            ->add('save', SubmitType::class, ['attr' => ['class' => 'btn-success btn-120']]);


    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ProductType::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'producttype',
        ));
    }
}