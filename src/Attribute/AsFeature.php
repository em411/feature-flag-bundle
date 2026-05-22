<?php

namespace Ajgarlag\FeatureFlagBundle\Attribute;

/**
 * Marks a class or method as a feature flag so it is autoconfigured.
 *
 * Usage as a Doctrine annotation (PHP 7.4):
 *
 *     @AsFeature
 *     @AsFeature("feature_name")
 *     @AsFeature(name="feature_name", method="resolve")
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
final class AsFeature
{
    /** @var string|null */
    public $name;

    /** @var string|null */
    public $method;

    /**
     * @param array<string, mixed>|string|null $name annotation values (Doctrine) or the feature name (direct use)
     */
    public function __construct($name = null, ?string $method = null)
    {
        if (\is_array($name)) {
            $values = $name;
            $this->name = $values['value'] ?? $values['name'] ?? null;
            $this->method = $values['method'] ?? null;

            return;
        }

        $this->name = $name;
        $this->method = $method;
    }
}
