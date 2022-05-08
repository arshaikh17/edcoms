<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomFieldData;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomFieldDataRadioArrayType extends CustomFieldDataType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm( $builder, $options);
        $builder->add('_type',HiddenType::class,array(
            "data"=> 'checkbox_array_type',
            "mapped"=> false
        ));

        $customField = $options['customField'];
        $options = explode(';', $customField->getOptions());
        $choices = array();
        foreach ($options as $o){
            $choices[$o] = $o;
        }
        $builder->add('value', ChoiceType::class,
            array(
                "label" => false,
                "required" => $customField->getRequired(),
                'choices'  => $choices,
                'multiple' => false,
                'expanded' => true
            ) );
    }

}