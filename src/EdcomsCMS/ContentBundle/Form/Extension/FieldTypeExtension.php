<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FieldTypeExtension extends AbstractTypeExtension
{

    /**
     * Add the help option
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array('help'));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (isset($options['help'])) {
            $builder->setAttribute('sonata_help', $options['help']);
        }
    }

    /**
     * Pass the help text to the view
     *
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($options['help'])) {
            $view->vars['sonata_help'] = $options['help'];
        }
    }

    public function getExtendedType()
    {
        return FormType::class;
    }
}