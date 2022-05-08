<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\AdminBundle\Form\Type;

use EdcomsCMS\AdminBundle\Form\EventListener\ResizePolyFormListener;
use Infinite\FormBundle\Form\Type\PolyCollectionType as InfinitePolyCollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PolyCollectionType extends InfinitePolyCollectionType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $typeAttr = $options['type_attr'];
        if($typeAttr){
            $options['type_name'] = $typeAttr;
        }
        $prototypes = $this->buildPrototypes($builder, $options);
        if ($options['allow_add'] && $options['prototype']) {
            $builder->setAttribute('prototypes', $prototypes);
        }

        $useTypesOptions = !empty($options['types_options']);

        $resizeListener = new ResizePolyFormListener(
            $prototypes,
            $useTypesOptions === true ? $options['types_options'] : $options['options'],
            $options['allow_add'],
            $options['allow_delete'],
            $options['type_name'],
            $options['index_property'],
            $useTypesOptions,
            $typeAttr
        );

        $builder->addEventSubscriber($resizeListener);
    }

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['prototype_name'] = $options['prototype_name'];
        $view->vars['collapsed'] = $options['collapsed'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault("type_attr",false);
        $resolver->setDefault("collapsed",true);
    }

    public function getBlockPrefix()
    {
        return 'edcoms_form_polycollection';
    }
}