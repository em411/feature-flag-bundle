<?php

namespace Ajgarlag\FeatureFlagBundle\DependencyInjection;

use Ajgarlag\FeatureFlagBundle\Provider\ProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Twig\Environment;

class FeatureFlagExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(\dirname(__DIR__).'/../config'));

        $loader->load('feature_flag.xml');

        $container->registerForAutoconfiguration(ProviderInterface::class)
            ->addTag('ajgarlag.feature_flag.provider');

        if (class_exists(Environment::class)) {
            $loader->load('feature_flag_twig.xml');
        }
    }
}
