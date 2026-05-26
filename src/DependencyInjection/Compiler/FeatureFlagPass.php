<?php

namespace Ajgarlag\FeatureFlagBundle\DependencyInjection\Compiler;

use Ajgarlag\FeatureFlagBundle\Attribute\AsFeature;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class FeatureFlagPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('ajgarlag.feature_flag.feature_checker')) {
            return;
        }

        $this->autoconfigureAnnotatedFeatures($container);

        $features = [];
        $serviceRefs = [];
        foreach ($container->findTaggedServiceIds('ajgarlag.feature_flag.feature') as $serviceId => $tags) {
            $className = $this->getServiceClass($container, $serviceId);
            $r = $container->getReflectionClass($className);

            if (null === $r) {
                throw new RuntimeException(\sprintf('Invalid service "%s": class "%s" does not exist.', $serviceId, $className));
            }

            foreach ($tags as $tag) {
                $featureName = ($tag['feature'] ?? '') ?: $className;
                if (\array_key_exists($featureName, $features)) {
                    throw new RuntimeException(\sprintf('Feature "%s" is already tagged on another service; feature names must be unique.', $featureName));
                }

                $method = $tag['method'] ?? '__invoke';
                if (!$r->hasMethod($method)) {
                    throw new RuntimeException(\sprintf('Invalid feature method "%s": method "%s::%s()" does not exist.', $serviceId, $r->getName(), $method));
                }
                if (!$r->getMethod($method)->isPublic()) {
                    throw new RuntimeException(\sprintf('Invalid feature method "%s": method "%s::%s()" must be public.', $serviceId, $r->getName(), $method));
                }

                $features[$featureName] = [$serviceId, $method];
                $serviceRefs[$serviceId] = new Reference($serviceId);
            }
        }

        $locator = ServiceLocatorTagPass::register($container, $serviceRefs);
        $container->getDefinition('ajgarlag.feature_flag.provider.in_memory')
            ->setArguments([$locator, $features]);

        if ($container->hasDefinition('profiler')) {
            $this->loadDebugDefinitions($container);
        }
    }

    private function autoconfigureAnnotatedFeatures(ContainerBuilder $container): void
    {
        $reader = new AnnotationReader();

        foreach ($container->getDefinitions() as $definition) {
            if (!$definition->isAutoconfigured() || $definition->isAbstract()) {
                continue;
            }

            $class = $definition->getClass();
            if (null === $class) {
                continue;
            }

            try {
                $r = $container->getReflectionClass($class, false);
            } catch (\ReflectionException $e) {
                continue;
            }

            if (null === $r) {
                continue;
            }

            try {
                $this->addAnnotationTags($reader, $r, $definition);
            } catch (AnnotationException $e) {
                // Ignore classes whose docblocks Doctrine cannot parse.
                continue;
            }
        }
    }

    private function addAnnotationTags(AnnotationReader $reader, \ReflectionClass $r, Definition $definition): void
    {
        foreach ($reader->getClassAnnotations($r) as $annotation) {
            if (!$annotation instanceof AsFeature) {
                continue;
            }

            $definition->addTag('ajgarlag.feature_flag.feature', [
                'feature' => $annotation->name ?? $r->getName(),
                'method' => $annotation->method ?? '__invoke',
            ]);
        }

        foreach ($r->getMethods() as $method) {
            foreach ($reader->getMethodAnnotations($method) as $annotation) {
                if (!$annotation instanceof AsFeature) {
                    continue;
                }

                if (null !== $annotation->method && $method->getName() !== $annotation->method) {
                    throw new LogicException(\sprintf('Using the @AsFeature(method: "%s") annotation on a method is not valid. Either remove the method value or move this to the top of the class (%s).', $annotation->method, $r->getName()));
                }

                $definition->addTag('ajgarlag.feature_flag.feature', [
                    'feature' => $annotation->name ?? $r->getName().'::'.$method->getName(),
                    'method' => $method->getName(),
                ]);
            }
        }
    }

    private function getServiceClass(ContainerBuilder $container, string $serviceId): ?string
    {
        while (true) {
            $definition = $container->findDefinition($serviceId);

            if (!$definition->getClass() && $definition instanceof ChildDefinition) {
                $serviceId = $definition->getParent();

                continue;
            }

            return $container->getParameterBag()->resolveValue($definition->getClass());
        }
    }

    private function loadDebugDefinitions(ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(\dirname(__DIR__).'/../../config'));

        $loader->load('feature_flag_debug.xml');
    }
}
