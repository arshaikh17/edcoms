<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\AdminBundle\Form\Type\Layout;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{

    const ONECOL = 'col-md-1';
    const TWOCOLS = 'col-md-2';
    const THREECOLS = 'col-md-3';
    const FOURCOLS = 'col-md-4';
    const FIVECOLS = 'col-md-5';
    const SIXCOLS = 'col-md-6';
    const SEVENCOLS = 'col-md-7';
    const EIGHTCOLS = 'col-md-8';
    const NINECOLS = 'col-md-9';
    const TENCOLS = 'col-md-10';
    const ELEVENCOLS = 'col-md-11';
    const TWELVECOLS = 'col-md-12';

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'inherit_data' => true,
           'label' => false,
           'group_label' => '',
           'description' => null,
           'size' => self::SIXCOLS
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['width_class'] = $options['size'];
        $view->vars['group_label'] = $options['group_label'];
        $view->vars['description'] = $options['description'];
    }

    public function getBlockPrefix()
    {
        return 'layout_group';
    }

}