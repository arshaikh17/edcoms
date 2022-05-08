<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\AuthBundle\Form\Type;

use EdcomsCMS\AdminBundle\Form\Type\PolyCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('contacts',PolyCollectionType::class,array(
                'types'=> array(
                    ContactType::class
                ),
                'types_options'=>array(
                    ContactType::class  => array(
                    ),
                ),
                'allow_add' => true,
                'allow_delete' => true
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EdcomsCMS\AuthBundle\Entity\Person'
        ));
    }
}