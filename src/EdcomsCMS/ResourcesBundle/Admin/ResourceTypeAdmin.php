<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\Admin;

use EdcomsCMS\ContentBundle\Form\Type\Media\MediaType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ResourceTypeAdmin extends AbstractAdmin
{

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
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
            ->add('active', null, array('editable' => true))
        ;
    }

}