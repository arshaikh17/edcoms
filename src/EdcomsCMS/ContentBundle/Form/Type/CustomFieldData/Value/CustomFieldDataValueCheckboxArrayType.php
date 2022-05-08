<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\Value;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;

class CustomFieldDataValueCheckboxArrayType extends ChoiceType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm( $builder, $options);
        $builder
            ->addModelTransformer(new CallbackTransformer(
                function ($choicesString) {
                    return explode(',',$choicesString);
                },
                function ($choicesArray) {
                   return is_array($choicesArray) ? implode(',',$choicesArray) : '';
                }
            ))
        ;
    }

}