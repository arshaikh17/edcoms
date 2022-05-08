<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\UserBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class RTBFUserExtensionPass implements CompilerPassInterface{

    public function process(ContainerBuilder $container)
    {
        if (!$container->has('edcoms.user.rtbf_extensions.pool')) {
            return;
        }

        $definition = $container->findDefinition('edcoms.user.rtbf_extensions.pool');

        $taggedServices = $container->findTaggedServiceIds('edcoms.user.rtbf_extension');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addRTBFUserExtension', array(new Reference($id)));
        }
    }

}