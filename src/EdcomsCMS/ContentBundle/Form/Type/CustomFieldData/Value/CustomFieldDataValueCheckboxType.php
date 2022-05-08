<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\Value;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;

class CustomFieldDataValueCheckboxType extends CheckboxType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm( $builder, $options);
        $builder
            ->addModelTransformer(new CallbackTransformer(
                function ($value) {
                    return $value ? true : false;
                },
                function ($value) {
                   return $value ? 1 : 0;
                }
            ))
        ;
    }

}