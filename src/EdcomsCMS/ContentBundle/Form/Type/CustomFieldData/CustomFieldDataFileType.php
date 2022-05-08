<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomFieldData;

use EdcomsCMS\ContentBundle\Form\Type\Media\MediaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class CustomFieldDataFileType extends CustomFieldDataType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm( $builder, $options);

        $customField = $options['customField'];

        $builder
            ->add('_type',HiddenType::class,array(
                "data"=> 'file_type',
                "mapped"=> false
            ))
            ->add('value', MediaType::class, array(
                "label" => false,
                "required" => $customField->getRequired()
            ))
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['baseDialogURL'] = '/cms/filemanager/dialog.php?&field_id=';
    }

    public function getBlockPrefix()
    {
        return 'custom_field_data_file';
    }
}