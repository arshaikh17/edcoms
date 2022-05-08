<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\CustomFieldData;

use EdcomsCMS\ContentBundle\Form\Type\Media\MediaType;
use EdcomsCMS\ContentBundle\Validator\Constraints\ValidVideo;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CustomFieldDataVideoType extends CustomFieldDataType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm( $builder, $options);

        $customField = $options['customField'];

        $builder
            ->add('_type',HiddenType::class,array(
                "data"=> 'video_type',
                "mapped"=> false
            ))
            ->add('value', MediaType::class, array(
                "label" => false,
                "required" => $customField->getRequired(),
                "video_only" => true,
                "constraints" =>  array(
                    new ValidVideo()
                )
            ))
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $data['value']=(int) preg_replace('/\\.[^.\\s]{3,4}$/', '', $data['value']);
            $event->setData($data);
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['baseDialogURL'] = '/cms/filemanager/dialog.php?type=3&field_id=';
    }

    public function getBlockPrefix()
    {
        return 'custom_field_data_file';
    }
}