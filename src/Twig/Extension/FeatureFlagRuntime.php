<?php

namespace Ajgarlag\FeatureFlagBundle\Twig\Extension;

use Ajgarlag\FeatureFlagBundle\FeatureCheckerInterface;

final class FeatureFlagRuntime
{
    /**
     * @var FeatureCheckerInterface
     */
    private $featureChecker;

    public function __construct(FeatureCheckerInterface $featureChecker)
    {
        $this->featureChecker = $featureChecker;
    }

    public function isEnabled(string $featureName): bool
    {
        return $this->featureChecker->isEnabled($featureName);
    }

    /**
     * @return mixed
     */
    public function getValue(string $featureName)
    {
        return $this->featureChecker->getValue($featureName);
    }
}
