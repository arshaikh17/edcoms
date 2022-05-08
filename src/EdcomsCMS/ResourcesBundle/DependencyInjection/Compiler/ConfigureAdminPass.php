<?php
/**
 * Created by PhpStorm.
 * User: dimitris
 */

namespace EdcomsCMS\ResourcesBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigureAdminPass implements CompilerPassInterface
{

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $config = $container->getParameter('edcoms_cms_resources.configuration');

        $admins = ['resource_subject', 'resource', 'resource_type', 'resource_topic', 'age_group', 'resource_activity'];

        foreach ($admins as $adminConfig){
            if(isset($config[$adminConfig])){
                $serviceName = $config[$adminConfig]['default_admin_service'];
                $useCustomAdmin = $config[$adminConfig]['custom_admin_service'];
                if($useCustomAdmin){
                    $container->removeDefinition($config[$adminConfig]['default_admin_service']);
                    $serviceName = $config[$adminConfig]['custom_admin_service'];
                }


                $adminClass = $container
                    ->getDefinition($serviceName)
                    ->replaceArgument(1, $config[$adminConfig]['entity_class'])
                ;

                if(!$useCustomAdmin){
                    $adminClass->setClass($config[$adminConfig]['admin']);
                }

                $sonataAdminTag = $adminClass->getTag('sonata.admin');
                $sonataAdminTag[0]['show_in_dashboard'] = $config[$adminConfig]['show_admin'];
                $tags = $adminClass->getTags();
                $tags['sonata.admin'] = $sonataAdminTag;
                $adminClass->setTags($tags);
            }
        }
    }

}