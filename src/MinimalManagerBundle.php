<?php

namespace MinimalOriginal\ManagerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use MinimalOriginal\ManagerBundle\DependencyInjection\Compiler\ModulePass;

class MinimalManagerBundle extends Bundle
{
  /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        
        $container->addCompilerPass(new ModulePass());

    }
}
