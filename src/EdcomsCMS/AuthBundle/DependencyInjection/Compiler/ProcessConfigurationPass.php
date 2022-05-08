<?php

/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\AuthBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ProcessConfigurationPass implements CompilerPassInterface
{

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasParameter('edcoms_cms_auth.show_admin') && $container->getParameter('edcoms_cms_auth.show_admin')==false) {
            $container->removeDefinition('edcoms.auth.admin.user');
        }
    }
}