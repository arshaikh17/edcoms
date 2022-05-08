<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomField;

use EdcomsCMS\AdminBundle\Form\Type\PolyCollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomFieldGroupType extends CustomFieldType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder,$options);

        $types = array(
            CustomFieldContentArrayType::class,
            CustomFieldTextType::class,
            CustomFieldTextAreaType::class,
            CustomFieldRichTextAreaType::class,
            CustomFieldCheckboxType::class,
            CustomFieldRadioArrayType::class,
            CustomFieldCheckboxArrayType::class,
            CustomFieldDateType::class,
            CustomFieldNumberType::class,
            CustomFieldFileType::class,
            CustomFieldImageType::class,
            CustomFieldVideoType::class,
            CustomFieldHiddenType::class,
        );

        $typesOptions = array(
            CustomFieldTextType::class => array(
                'button_label' => 'Text'
            ),
            CustomFieldContentArrayType::class => array(
                'button_label' => 'Content Array'
            ),
            CustomFieldTextAreaType::class => array(
                'button_label' => 'Textarea'
            ),
            CustomFieldRichTextAreaType::class => array(
                'button_label' => 'Rich Textarea'
            ),
            CustomFieldCheckboxType::class => array(
                'button_label' => 'Checkbox'
            ),
            CustomFieldRadioArrayType::class => array(
                'button_label' => 'Radio Array'
            ),
            CustomFieldCheckboxArrayType::class => array(
                'button_label' => 'Checkbox Array'
            ),
            CustomFieldDateType::class => array(
                'button_label' => 'Date'
            ),
            CustomFieldNumberType::class => array(
                'button_label' => 'Number'
            ),
            CustomFieldFileType::class => array(
                'button_label' => 'File'
            ),
            CustomFieldImageType::class => array(
                'button_label' => 'Image'
            ),
            CustomFieldVideoType::class => array(
                'button_label' => 'Video'
            ),
            CustomFieldHiddenType::class => array(
                'button_label' => 'Hidden'
            )
        );

        if($options['group_depth']>=1){
            $types[] = CustomFieldGroupType::class;
            $typesOptions[CustomFieldGroupType::class] =  array(
                'button_label' => 'Group',
                "group_depth" => $options['group_depth'] - 1
            );
        }

        $builder->add('children',PolyCollectionType::class,array(
            'prototype_name' => sprintf('__nestedpoly-%s__',$options['group_depth']),
            'attr'=> array('class'=>'content-type-custom-fields'),
            'type_attr' => 'fieldType',
            'types' => $types,
            'types_options' => $typesOptions,
            'allow_add' => true,
            'allow_delete' => true
        ))
        ->add('fieldType',HiddenType::class,array(
            'data'=>'group'
        ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'group_depth' => 1
        ));
    }
}