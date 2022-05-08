<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\AdminBundle\Form\Type\Media;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FileType extends TextType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm( $builder, $options);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['file_type'] = $options['file_type'];
        $view->vars['dialog_url'] = sprintf("/cms/filemanager/dialog.php?field_id=%s",$view->vars['id']);
//        $view->vars['dialog_url'] = sprintf("/cms/filemanager/dialog.php?field_id=%s&type=%s",$view->vars['id'],$options['file_type']=="image" ? 1 : 3);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('attr', array(
                "class"=>"custom-field-file-picker hidden",
                "data-file-url"=> ""
                ));
        $resolver->setDefault('file_type','image');
    }

    public function getBlockPrefix()
    {
        return 'edcoms_file';
    }
}