<?php

namespace EdcomsCMS\ResourcesBundle;

use EdcomsCMS\ResourcesBundle\DependencyInjection\Compiler\AttachConfigPass;
use EdcomsCMS\ResourcesBundle\DependencyInjection\Compiler\ConfigureAdminPass;
use EdcomsCMS\ResourcesBundle\DependencyInjection\Compiler\FilterBuilderPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;

class EdcomsCMSResourcesBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AttachConfigPass());
        $container->addCompilerPass(new ConfigureAdminPass());
        $container->addCompilerPass(new FilterBuilderPass());
        $this->registerDoctrine($container);
    }

    private function registerDoctrine(ContainerBuilder $container){
        $entityContextDir = realpath(__DIR__.'/EntityContext');
        $mappings = array(
            $entityContextDir => 'EdcomsCMS\ResourcesBundle\EntityContext',
        );

        if (class_exists(DoctrineOrmMappingsPass::class)) {
            $container->addCompilerPass(
                DoctrineOrmMappingsPass::createAnnotationMappingDriver(
                    $mappings,
                    array($entityContextDir)
                ));
        }

    }

}
