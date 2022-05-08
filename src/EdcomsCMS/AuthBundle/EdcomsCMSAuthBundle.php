<?php

namespace EdcomsCMS\AuthBundle;

use EdcomsCMS\AuthBundle\DependencyInjection\Compiler\ProcessConfigurationPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EdcomsCMSAuthBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ProcessConfigurationPass());
    }
}
