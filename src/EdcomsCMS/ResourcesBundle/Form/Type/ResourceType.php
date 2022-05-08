<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use EdcomsCMS\AdminBundle\Form\Type\RichTextAreaType;
use EdcomsCMS\ContentBundle\Form\Type\Media\MediaType;
use EdcomsCMS\ResourcesBundle\Entity\Resource;
use EdcomsCMS\ResourcesBundle\Model\AgeGroupInterface;
use EdcomsCMS\ResourcesBundle\Service\EdcomsResourcesConfigurationService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ResourceType extends AbstractType
{

    /**
     * @var EdcomsResourcesConfigurationService
     */
    private $edcomsResourcesConfiguration;

    public function __construct(EdcomsResourcesConfigurationService $edcomsResourcesConfiguration)
    {
        $this->edcomsResourcesConfiguration = $edcomsResourcesConfiguration;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('subtitle')
            ->add('subjects')
            ->add('topics')
            ->add('type')
            ->add('summary')
            ->add('content', RichTextAreaType::class, array(
                "required" => true
            ))
            ->add('curriculumContent', RichTextAreaType::class, array(
                "required" => false
            ))
            ->add('quickViewContent', TextareaType::class, array(
                "required" => false,
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'advanced' // Skip it if you want to use default theme
                )
            ))
            ->add('ageGroups', EntityType::class,[
                'class' => $this->edcomsResourcesConfiguration->getEntityClass(EdcomsResourcesConfigurationService::AGE_GROUP),
                'multiple' => true,
                'query_builder' => function(EntityRepository $er) {
                    return $er
                        ->createQueryBuilder('age')
                        ->where("age.active=true")
                        ;
                }
            ])
            ->add('activities')
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
            ->add('file', MediaType::class,
                [
                    'label' => "File",
                    'required' => false,
                    'media_object' => true
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Resource::class
        ));
    }
}