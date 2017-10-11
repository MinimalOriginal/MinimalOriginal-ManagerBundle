<?php

namespace MinimalOriginal\ManagerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;


class MinimalManagerExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        if( true === isset($config['module']) && true === is_array($config['module'])){
          $container->setParameter('minimal_manager.module',$config['module']);
        }
    }

    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        $file = __DIR__.'/../Resources/config/ivory_ck_editor_config.yml';
        $ivory_ck_editor_config = Yaml::parse(file_get_contents($file));

        foreach ($container->getExtensions() as $name => $extension) {
            switch ($name) {
              case 'ivory_ck_editor':
                  $container->prependExtensionConfig($name, $ivory_ck_editor_config);
                  break;
            }
        }
    }
}
