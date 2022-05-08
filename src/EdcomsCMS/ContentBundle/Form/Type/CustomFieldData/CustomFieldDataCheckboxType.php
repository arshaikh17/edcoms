<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomFieldData;

use EdcomsCMS\ContentBundle\Form\Type\CustomFieldData\Value\CustomFieldDataValueCheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * Class CustomFieldDataCheckboxType
 * @package EdcomsCMS\ContentBundle\Form\Type\CustomFieldData
 */
class CustomFieldDataCheckboxType extends CustomFieldDataType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm( $builder, $options);
        $builder->add('_type',HiddenType::class,array(
            "data"=> 'checkbox_type',
            "mapped"=> false
        ));

        $builder->add('value', CustomFieldDataValueCheckboxType::class,
            array(
                "label" => false,
                "required" => false,
            ) );
    }
}