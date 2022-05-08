<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\AdminBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RichTextAreaType extends TextareaType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        // ensure that both options exist even when the options are overwritten
        $resolver->setNormalizer('attr', function(Options $options, $value){
            return array_merge([
                    'class' => 'tinymce',
                    'data-theme' => 'advanced'
                ], $value);
        });
        $resolver->setDefault('attr', [
            'class' => 'tinymce',
            'data-theme' => 'advanced'
        ]);
        parent::configureOptions($resolver);
    }
}
