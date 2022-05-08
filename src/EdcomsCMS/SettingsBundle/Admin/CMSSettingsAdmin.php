<?php
/**
 * Created by Redi Linxa
 * Date: 20.11.19
 * Time: 11:10
 */

namespace EdcomsCMS\SettingsBundle\Admin;


use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

class CMSSettingsAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'general-settings';
    protected $baseRoutePattern = 'general-settings';

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept('list');
    }
}