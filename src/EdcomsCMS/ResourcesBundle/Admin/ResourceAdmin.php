<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Admin;

use EdcomsCMS\AdminBundle\Form\Type\RichTextAreaType;
use EdcomsCMS\ContentBundle\Form\Type\Media\MediaType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Valid;

class ResourceAdmin extends AbstractAdmin
{

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('title')
            ->add('subtitle')
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
            ->add('subjects')
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

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title')
            ->add('slug')
            ->add('useAsFilter', null, array('editable' => true))
        ;
    }

}