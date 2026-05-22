<?php

namespace Ajgarlag\FeatureFlagBundle\Provider;

use Psr\Container\ContainerInterface;

/**
 * Provides features whose logic is a method call on a lazily-located service.
 *
 * Used by the bundle to expose features declared with the @AsFeature annotation
 * or the "ajgarlag.feature_flag.feature" service tag. The feature service is
 * only instantiated when its closure is actually invoked.
 */
final class ServiceProvider implements ProviderInterface
{
    /**
     * @var ContainerInterface
     */
    private $locator;

    /**
     * @var array<string, array{0: string, 1: string}>
     */
    private $features;

    /**
     * @param ContainerInterface                          $locator  PSR-11 locator of feature services
     * @param array<string, array{0: string, 1: string}> $features map of feature name => [serviceId, method]
     */
    public function __construct(ContainerInterface $locator, array $features = [])
    {
        $this->locator = $locator;
        $this->features = $features;
    }

    public function get(string $featureName): ?callable
    {
        if (!isset($this->features[$featureName])) {
            return null;
        }

        $locator = $this->locator;
        [$serviceId, $method] = $this->features[$featureName];

        return static function () use ($locator, $serviceId, $method) {
            return $locator->get($serviceId)->{$method}();
        };
    }
}
