<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Form\Type;

use EdcomsCMS\ContentBundle\Form\Type\Media\MediaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceTypeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('summary', TextType::class, [
                'required' => false
            ])
            ->add('thumbImage', MediaType::class,[
                'label' => "Thumbnail",
                'required' => false,
                'media_object' => true
            ])
            ->add('headerImage', MediaType::class,
            [
                'label' => "Header Image",
                'required' => false,
                'media_object' => true
            ])
            ->add('useAsFilter')
            ->add('active')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => \EdcomsCMS\ResourcesBundle\Entity\ResourceType::class
        ));
    }
}