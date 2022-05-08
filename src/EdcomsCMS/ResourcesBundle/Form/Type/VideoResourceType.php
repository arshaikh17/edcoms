<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Form\Type;

use EdcomsCMS\AdminBundle\Form\Type\Media\FileType;
use EdcomsCMS\ResourcesBundle\Entity\VideoResource;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VideoResourceType extends ResourceType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('video', MediaType::class,
                [
                    'label' => "Video",
                    'required' => false,
                    'media_object' => true
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => VideoResource::class
        ));
    }
}