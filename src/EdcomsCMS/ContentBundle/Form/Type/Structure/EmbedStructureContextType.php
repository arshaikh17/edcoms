<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Form\Type\Structure;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class EmbedStructureContextType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($options['context']){
            $builder->add('context',$options['context']->getForm(),array(
                "label" => false,
                "data_class" => $options['context']->getContextClass()
            ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EdcomsCMS\ContentBundle\Entity\StructureContext',
            'context' => null
        ));
    }
}