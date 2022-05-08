<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ContentBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class MediaTypeAdmin extends AbstractAdmin
{

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('target')
            ->add('filetype',ChoiceType::class,array(
                "choices" => array(
                    'Audio mp4'                     =>      'audio/mp4',
                    'CSV'                           =>      'text/csv',
                    'Excel (.xls)'                  =>      'application/vnd.ms-excel',
                    'Excel (.xlsx)'                 =>      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Generic'                       =>      '*/*',
                    'PDF'                           =>      'application/pdf',
                    'Powerpoint (.pps)'             =>      'application/vnd.ms-powerpoint',
                    'Powerpoint (.ppsx)'            =>      'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
                    'Powerpoint (.ppt)'             =>      'application/vnd.ms-powerpoint',
                    'Powerpoint (.pptx)'            =>      'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'Text'                          =>      'text/plain',
                    'Image jpeg'                    =>      'image/jpeg',
                    'Image png'                     =>      'image/png',
                    'Image gif'                     =>      'image/gif',
                    'Image bmp'                     =>      'image/bmp',
                    'Image svg'                     =>      'image/svg+xml',
                    'Video mp4'                     =>      'video/mp4',
                    'Video m4v'                     =>      'video/x-m4v',
                    'Video mov'                     =>      'video/quicktime',
                    'Video Text Track (captions)'   =>      'text/vtt',
                    'Word (.doc)'                   =>      'application/msword',
                    'Word (.docx)'                  =>      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'Zip'                           =>      'application/zip',
                    'Zip (compressed)'              =>      'application/x-zip-compressed'
                )
            ))
            ->add('compression',NumberType::class)
            ->add('width',NumberType::class)
            ->add('height',NumberType::class)
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('filetype')
            ->add('target')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('filetype')
            ->add('target')
        ;
    }
}
