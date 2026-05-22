<?php

namespace Ajgarlag\FeatureFlagBundle\Provider;

final class ChainProvider implements ProviderInterface
{
    /**
     * @var iterable<ProviderInterface>
     */
    private $providers;

    /**
     * @param iterable<ProviderInterface> $providers
     */
    public function __construct(iterable $providers = [])
    {
        $this->providers = $providers;
    }

    public function get(string $featureName): ?callable
    {
        foreach ($this->providers as $provider) {
            if ($feature = $provider->get($featureName)) {
                return $feature;
            }
        }

        return null;
    }
}
