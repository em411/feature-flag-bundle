<?php

namespace Ajgarlag\FeatureFlagBundle\Tests\Provider;

use Ajgarlag\FeatureFlagBundle\Provider\ServiceProvider;
use Ajgarlag\FeatureFlagBundle\Tests\Fixtures\ClassFeature;
use Ajgarlag\FeatureFlagBundle\Tests\Fixtures\MethodFeature;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

class ServiceProviderTest extends TestCase
{
    /**
     * @var ServiceProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $locator = new ServiceLocator([
            ClassFeature::class => static function () { return new ClassFeature(); },
            MethodFeature::class => static function () { return new MethodFeature(); },
        ]);

        $this->provider = new ServiceProvider($locator, [
            'class' => [ClassFeature::class, '__invoke'],
            'method_string' => [MethodFeature::class, 'string'],
        ]);
    }

    public function testGetInvokesTheServiceMethod(): void
    {
        $feature = $this->provider->get('class');

        $this->assertIsCallable($feature);
        $this->assertTrue($feature());
    }

    public function testGetUsesTheConfiguredMethod(): void
    {
        $feature = $this->provider->get('method_string');

        $this->assertIsCallable($feature);
        $this->assertSame('green', $feature());
    }

    public function testGetIsLazy(): void
    {
        // Resolving the closure must not touch the locator yet.
        $feature = $this->provider->get('class');
        $this->assertIsCallable($feature);
    }

    public function testGetNotFound(): void
    {
        $this->assertNull($this->provider->get('unknown'));
    }
}
