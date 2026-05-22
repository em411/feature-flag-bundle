<?php

namespace Ajgarlag\FeatureFlagBundle;

use Ajgarlag\FeatureFlagBundle\DependencyInjection\Compiler\FeatureFlagPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FeatureFlagBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new FeatureFlagPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
    }
}
