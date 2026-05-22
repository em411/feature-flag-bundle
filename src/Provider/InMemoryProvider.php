<?php

namespace Ajgarlag\FeatureFlagBundle\Provider;

final class InMemoryProvider implements ProviderInterface
{
    /**
     * @var array<string, callable(): mixed>
     */
    private $features;

    /**
     * @param array<string, callable(): mixed> $features
     */
    public function __construct(array $features)
    {
        $this->features = $features;
    }

    public function get(string $featureName): ?callable
    {
        return $this->features[$featureName] ?? null;
    }
}
