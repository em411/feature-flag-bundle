<?php

namespace Ajgarlag\FeatureFlagBundle;

use Ajgarlag\FeatureFlagBundle\Provider\ProviderInterface;
use Symfony\Contracts\Service\ResetInterface;

final class FeatureChecker implements FeatureCheckerInterface, ResetInterface
{
    /**
     * @var ProviderInterface
     */
    private $provider;

    /**
     * @var array<string, mixed>
     */
    private $cache = [];

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function isEnabled(string $featureName): bool
    {
        return true === $this->getValue($featureName);
    }

    public function getValue(string $featureName)
    {
        if (\array_key_exists($featureName, $this->cache)) {
            return $this->cache[$featureName];
        }

        $feature = $this->provider->get($featureName) ?? static function () {
            return false;
        };

        return $this->cache[$featureName] = $feature();
    }

    public function reset(): void
    {
        $this->cache = [];
    }
}
