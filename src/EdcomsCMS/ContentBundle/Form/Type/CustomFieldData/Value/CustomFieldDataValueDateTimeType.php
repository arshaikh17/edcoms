<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\Value;

use Sonata\CoreBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;

class CustomFieldDataValueDateTimeType extends DateTimePickerType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder,$options);
        $builder
            ->addModelTransformer(new CallbackTransformer(
                function ($dateAsString) {
                    // transform string to DateTime
                    return $dateAsString!='' ? new \DateTime($dateAsString) : new \DateTime();
                },
                function ($dateAsObject)  use($options) {
                    // transform DateTime to string
                   return $dateAsObject ? $dateAsObject->format('Y-m-d H:i:s') : $dateAsObject;
                }
            ))
        ;
    }
}