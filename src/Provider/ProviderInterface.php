<?php

namespace Ajgarlag\FeatureFlagBundle\Provider;

/**
 * Represents a class that provides feature flags.
 *
 * A provider is responsible for retrieving the logic (as a callable) associated
 * with a feature flag name. This allows the feature flag system to be
 * decoupled from the actual source of the feature flag definitions (e.g.
 * configuration, database, or a remote service).
 */
interface ProviderInterface
{
    /**
     * @return callable(): mixed|null
     */
    public function get(string $featureName): ?callable;
}
