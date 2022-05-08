<?php

namespace EdcomsCMS\ContentBundle;

use EdcomsCMS\ContentBundle\DependencyInjection\Compiler\AddFormFieldsPass;
use EdcomsCMS\ContentBundle\DependencyInjection\Compiler\AttachConfigPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EdcomsCMSContentBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AttachConfigPass());
        $container->addCompilerPass(new AddFormFieldsPass());
    }

}
