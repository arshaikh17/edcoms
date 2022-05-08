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
use Sonata\AdminBundle\Route\RouteCollection;

class URLRedirectUsageAdmin extends AbstractAdmin
{

    /**
     * @var array
     */
    protected $datagridValues = array(
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt'
    );

    protected $parentAssociationMapping = 'urlRedirect';

    protected $maxPerPage = 200;

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('ipAddress')
            ->add('createdAt')
            ->add('userAgent')
            ->add('referrer')
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('create')
            ->remove('export')
        ;
    }
}