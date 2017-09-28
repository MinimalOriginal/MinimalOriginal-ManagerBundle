<?php

namespace MinimalOriginal\ManagerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ModulePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('minimal_manager.module_list')) {
            return;
        }

        $definition = $container->getDefinition('minimal_manager.module_list');
        foreach ($container->findTaggedServiceIds('minimal_manager.module') as $id => $attrs) {

            $definition->addMethodCall('addModule', array(new Reference($id)));

            // if (true === isset($attrs[0]) && true === isset($attrs[0]['class'])) {
            //     if (true === class_exists($attrs[0]['class'])) {
            //         $modelDefinition = $container->getDefinition($id);
            //         $modelDefinition->addMethodCall('setClassName', array($attrs[0]['class']));
            //     } else {
            //         throw new \InvalidArgumentException(sprintf('ttadmin.model attribute "class": "%s", does not exist.', $attrs[0]['class']));
            //     }
            // } else {
            //     throw new \InvalidArgumentException('ttadmin.model attribute "class" is not defined.');
            // }
        }
    }
}
