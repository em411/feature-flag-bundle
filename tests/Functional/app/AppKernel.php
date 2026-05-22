<?php

namespace Ajgarlag\FeatureFlagBundle\Tests\Functional\app;

use Ajgarlag\FeatureFlagBundle\FeatureFlagBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Minimal, self-contained kernel for the bundle's functional tests.
 *
 * Each instance loads a single YAML config file from app/config/.
 */
class AppKernel extends Kernel
{
    /**
     * @var string
     */
    private $config;

    public function __construct(string $config, bool $debug = true)
    {
        $this->config = $config;

        parent::__construct('test', $debug);
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new FeatureFlagBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/'.$this->config);
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/ajgarlag_feature_flag_bundle/'.md5($this->config).'/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/ajgarlag_feature_flag_bundle/'.md5($this->config).'/log';
    }
}
