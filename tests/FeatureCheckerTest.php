<?php

namespace Ajgarlag\FeatureFlagBundle\Tests;

use Ajgarlag\FeatureFlagBundle\FeatureChecker;
use Ajgarlag\FeatureFlagBundle\Provider\InMemoryProvider;
use PHPUnit\Framework\TestCase;

class FeatureCheckerTest extends TestCase
{
    /**
     * @var FeatureChecker
     */
    private $featureChecker;

    protected function setUp(): void
    {
        $this->featureChecker = new FeatureChecker(new InMemoryProvider([
            'feature_true' => static function () { return true; },
            'feature_false' => static function () { return false; },
            'feature_integer' => static function () { return 42; },
            'feature_random' => static function () { return random_int(1, 42); },
        ]));
    }

    public function testGetValue(): void
    {
        $this->assertSame(42, $this->featureChecker->getValue('feature_integer'));
    }

    public function testGetValueCache(): void
    {
        $this->assertIsInt($value = $this->featureChecker->getValue('feature_random'));
        $this->assertSame($value, $this->featureChecker->getValue('feature_random'));
    }

    public function testGetValueOnNotFound(): void
    {
        $this->assertFalse($this->featureChecker->getValue('unknown_feature'));
    }

    /**
     * @dataProvider provideIsEnabled
     */
    public function testIsEnabled(string $featureName, bool $expectedResult): void
    {
        $this->assertSame($expectedResult, $this->featureChecker->isEnabled($featureName));
    }

    public static function provideIsEnabled(): iterable
    {
        yield '"true"' => ['feature_true', true];
        yield '"false"' => ['feature_false', false];
        yield 'an integer' => ['feature_integer', false];
        yield 'an unknown feature' => ['unknown_feature', false];
    }
}
