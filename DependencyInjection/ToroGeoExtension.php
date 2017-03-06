<?php

namespace Toro\Bundle\GeoBundle\DependencyInjection;

use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Toro\Bundle\GeoBundle\ToroGeoBundle;

class ToroGeoExtension extends AbstractResourceExtension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $container->setParameter('toro.scope.zone', $config['scopes']);
        $this->registerResources(ToroGeoBundle::APPLICATION_NAME, $config['driver'], $config['resources'], $container);
        $loader->load('services.xml');

        if ($config['fixture']) {
            $loader->load('fixture/services.xml');
        }
    }
}
