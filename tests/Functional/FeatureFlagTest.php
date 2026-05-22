<?php

namespace Ajgarlag\FeatureFlagBundle\Tests\Functional;

use Ajgarlag\FeatureFlagBundle\FeatureCheckerInterface;
use Ajgarlag\FeatureFlagBundle\Tests\Fixtures\ClassFeature;
use Ajgarlag\FeatureFlagBundle\Tests\Fixtures\ClassMethodFeature;
use Ajgarlag\FeatureFlagBundle\Tests\Fixtures\NamedFeature;
use Ajgarlag\FeatureFlagBundle\Tests\Functional\app\AppKernel;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

class FeatureFlagTest extends TestCase
{
    /**
     * @var string
     */
    private static $cacheBase;

    public static function setUpBeforeClass(): void
    {
        self::$cacheBase = sys_get_temp_dir().'/ajgarlag_feature_flag_bundle';
    }

    protected function setUp(): void
    {
        (new Filesystem())->remove(self::$cacheBase);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove(self::$cacheBase);
    }

    private function bootContainer(string $config): ContainerInterface
    {
        require_once __DIR__.'/app/AppKernel.php';

        $kernel = new AppKernel($config, true);
        $kernel->boot();

        return $kernel->getContainer();
    }

    public function testFeatureFlagAssertions(): void
    {
        $container = $this->bootContainer('config.yml');
        /** @var FeatureCheckerInterface $featureChecker */
        $featureChecker = $container->get('test.ajgarlag.feature_flag.feature_checker');

        // With default behavior
        $this->assertTrue($featureChecker->isEnabled(ClassFeature::class));
        $this->assertTrue($featureChecker->isEnabled(ClassMethodFeature::class));

        // With a custom name
        $this->assertTrue($featureChecker->isEnabled('custom_name'));
        $this->assertFalse($featureChecker->isEnabled(NamedFeature::class));

        // With an unknown feature
        $this->assertFalse($featureChecker->isEnabled('unknown'));

        // Get values
        $this->assertSame('green', $featureChecker->getValue('method_string'));
        $this->assertSame(42, $featureChecker->getValue('method_int'));
    }

    public function testFeatureFlagAssertionsWithInvalidMethod(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid feature method "Ajgarlag\FeatureFlagBundle\Tests\Fixtures\InvalidMethodFeature": method "Ajgarlag\FeatureFlagBundle\Tests\Fixtures\InvalidMethodFeature::invalid_method()" does not exist.');

        $this->bootContainer('config_with_invalid_method.yml');
    }

    public function testFeatureFlagAssertionsWithInvalidMethodVisibility(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid feature method "Ajgarlag\FeatureFlagBundle\Tests\Fixtures\InvalidMethodVisibilityFeature": method "Ajgarlag\FeatureFlagBundle\Tests\Fixtures\InvalidMethodVisibilityFeature::resolve()" must be public.');

        $this->bootContainer('config_with_invalid_method_visibility.yml');
    }

    public function testFeatureFlagAssertionsWithDifferentMethod(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Using the @AsFeature(method: "different") annotation on a method is not valid. Either remove the method value or move this to the top of the class (Ajgarlag\FeatureFlagBundle\Tests\Fixtures\DifferentMethodFeature).');

        $this->bootContainer('config_with_different_method.yml');
    }

    public function testFeatureFlagAssertionsWithDuplicate(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Feature "Ajgarlag\FeatureFlagBundle\Tests\Fixtures\ClassFeature" already defined in the "ajgarlag.feature_flag.provider.in_memory" provider.');

        $this->bootContainer('config_with_duplicate.yml');
    }
}
