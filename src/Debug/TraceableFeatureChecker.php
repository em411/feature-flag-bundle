<?php

namespace Ajgarlag\FeatureFlagBundle\Debug;

use Ajgarlag\FeatureFlagBundle\FeatureCheckerInterface;
use Symfony\Contracts\Service\ResetInterface;

final class TraceableFeatureChecker implements FeatureCheckerInterface
{
    public const STATUS_ENABLED = 'enabled';
    public const STATUS_DISABLED = 'disabled';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_NOT_FOUND = 'not_found';

    /**
     * @var FeatureCheckerInterface
     */
    private $decorated;

    /**
     * @var array<string, array{status: string, value: mixed, calls: int}>
     */
    private $resolvedValues = [];

    public function __construct(FeatureCheckerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function isEnabled(string $featureName): bool
    {
        $isEnabled = $this->decorated->isEnabled($featureName);

        // Force logging value. It has no cost since value is cached by the decorated FeatureChecker.
        $this->getValue($featureName);

        $this->resolvedValues[$featureName]['status'] = $isEnabled ? self::STATUS_ENABLED : self::STATUS_DISABLED;

        return $isEnabled;
    }

    /**
     * @return mixed
     */
    public function getValue(string $featureName)
    {
        $value = $this->decorated->getValue($featureName);

        $this->resolvedValues[$featureName] = $this->resolvedValues[$featureName] ?? [
            'status' => self::STATUS_RESOLVED,
            'value' => $value,
            'calls' => 0,
        ];

        ++$this->resolvedValues[$featureName]['calls'];

        return $value;
    }

    /**
     * @return array<string, array{status: string, value: mixed, calls: int}>
     */
    public function getResolvedValues(): array
    {
        return $this->resolvedValues;
    }

    public function reset(): void
    {
        $this->resolvedValues = [];
        if ($this->decorated instanceof ResetInterface || method_exists($this->decorated, 'reset')) {
            $this->decorated->reset();
        }
    }
}
