<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomFieldData;

use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\Value\CustomFieldDataValueNumberType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Valid;

class CustomFieldDataNumberType extends CustomFieldDataType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $customField = $options['customField'];

        $builder
            ->add('_type',HiddenType::class,array(
                "data"=> 'text_type',
                "mapped"=> false
            ))
            ->add('value', CustomFieldDataValueNumberType::class, array(
                "label" => false,
                "required" => $customField->getRequired(),
                "empty_data" =>  ''
            ))
        ;
    }
}