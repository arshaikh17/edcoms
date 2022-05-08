<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Form\Type;

use EdcomsCMS\ResourcesBundle\Entity\ResourceSubject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceSubjectType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('summary')
            ->add('active')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ResourceSubject::class
        ));
    }
}