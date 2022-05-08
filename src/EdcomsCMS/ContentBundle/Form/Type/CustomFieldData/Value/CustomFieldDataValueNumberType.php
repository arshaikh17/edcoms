<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\Value;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;

class CustomFieldDataValueNumberType extends NumberType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm( $builder, $options);
        $builder
            ->addModelTransformer(new CallbackTransformer(
                function ($value) {
                    return is_numeric($value) ? $value : 0;
                },
                function ($value) {
                   return is_numeric($value) ? $value : null;
                }
            ))
        ;
    }

}