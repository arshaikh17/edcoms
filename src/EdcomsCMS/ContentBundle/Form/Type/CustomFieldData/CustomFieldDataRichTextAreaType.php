<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomFieldData;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Valid;

class CustomFieldDataRichTextAreaType extends CustomFieldDataType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $customField = $options['customField'];

        $builder
            ->add('_type',HiddenType::class,array(
                "data"=> 'textarea_type',
                "mapped"=> false
            ))
            ->add('value', TextareaType::class, array(
                "label" => false,
                "required" => $customField->getRequired(),
                "empty_data" =>  '',
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'advanced' // Skip it if you want to use default theme
                )
            ));
    }
}