<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomFieldData;

use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\Value\CustomFieldDataValueCheckboxArrayType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Valid;

class CustomFieldDataCheckboxArrayType extends CustomFieldDataType
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
        $builder->add('value', CustomFieldDataValueCheckboxArrayType::class,
            array(
                "label" => false,
                "required" => false,
                'choices'  => $choices,
                'multiple' => true,
                'expanded' => true,
            ) );
    }

}