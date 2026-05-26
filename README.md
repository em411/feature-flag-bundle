# FeatureFlag Bundle

[![CI](https://github.com/em411/feature-flag-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/em411/feature-flag-bundle/actions/workflows/ci.yml)
[![Latest Stable Version](https://poser.pugx.org/em411/feature-flag-bundle/v)](https://packagist.org/packages/em411/feature-flag-bundle)
[![License](https://poser.pugx.org/em411/feature-flag-bundle/license)](https://packagist.org/packages/em411/feature-flag-bundle)

The FeatureFlag Bundle allows you to split the code execution flow by enabling features depending on context.

It provides a service that checks if a feature is enabled. Each feature is defined by a callable function that returns a
value.
The feature is enabled if the value matches the expected one (mostly a boolean but not limited to).

**This bundle code has been borrowed from https://github.com/symfony/symfony/pull/53213**.

> **Symfony 4.4 / PHP 7.4 backport.** This is a fork of [ajgarlag/feature-flag-bundle](https://github.com/ajgarlag/feature-flag-bundle) refactored to run on Symfony 4.4 and PHP 7.4.

## Which package should I use?

- **Running Symfony 6+ / PHP 8.1+?** Use the upstream [`ajgarlag/feature-flag-bundle`](https://github.com/ajgarlag/feature-flag-bundle) — it targets modern Symfony and uses native PHP 8 attributes.
- **Stuck on Symfony 4.4 / PHP 7.4?** Use this fork. It exists because the upstream cannot run on legacy stacks. The public API is intentionally kept compatible with the upstream so migration is a namespace-free drop-in.

> **Note on the namespace.** The Composer package is `em411/feature-flag-bundle` but the PHP namespace remains `Ajgarlag\FeatureFlagBundle\` on purpose, so code written against the upstream works here unchanged. Imports and the bundle class reference (`Ajgarlag\FeatureFlagBundle\FeatureFlagBundle::class`) stay the same.

## 🚀 Getting Started

Follow these steps to install and use the bundle in your Symfony application.

### Step 1: Download the bundle

Open a command console, enter your project directory and execute the following command to download the latest stable
version of this bundle:

```bash
composer require em411/feature-flag-bundle
```

### Step 2: Enable the bundle (for non-Flex applications)

Then, enable the bundle by adding it to the list of registered bundles in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Ajgarlag\FeatureFlagBundle\FeatureFlagBundle::class => ['all' => true],
];
```

## ✨ Declaring features with annotations

You can declare features using the `@AsFeature` annotation. This allows you to autoconfigure your features as services.

### On a class

You can use the annotation on an invokable class:

```php
namespace App\Feature;

use Ajgarlag\FeatureFlagBundle\Attribute\AsFeature;

/**
 * @AsFeature("xmas")
 */
final class XmasFeature
{
    public function __invoke(): bool
    {
        return date('m-d') === '12-25';
    }
}
```

The feature will be named `xmas`. If you don't provide a name, the FQCN of the class will be used.

You can also use the `method` property of the annotation to specify a method to call on the service.

```php
namespace App\Feature;

use Ajgarlag\FeatureFlagBundle\Attribute\AsFeature;

/**
 * @AsFeature(name="xmas", method="isXmas")
 */
final class XmasFeature
{
    public function isXmas(): bool
    {
        return date('m-d') === '12-25';
    }
}
```

### On a method

You can also use the annotation on a method of a service. The method must be public.

```php
namespace App\Feature;

use Ajgarlag\FeatureFlagBundle\Attribute\AsFeature;

final class FeatureService
{
    /**
     * @AsFeature(name="weekend")
     */
    public function isWeekend(): bool
    {
        return date('N') >= 6;
    }

    /**
     * @AsFeature
     */
    // The feature will be named "App\Feature\FeatureService::isAnotherFeature"
    public function isAnotherFeature(): bool
    {
        return true;
    }
}
```

> **Note:** Unlike PHP 8 attributes, a Doctrine annotation cannot be repeated on the same class or method. To declare multiple features on one class, use the `ajgarlag.feature_flag.feature` service tag.

## Gating routes by a feature

Symfony 4.4 cannot evaluate services inside route `condition` expressions, so
route-level feature gating is done in the controller (or a `kernel.controller`
listener). Inject `FeatureCheckerInterface` and throw a 404 when the feature is
off:

```php
use Ajgarlag\FeatureFlagBundle\FeatureCheckerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CheckoutController
{
    private $featureChecker;

    public function __construct(FeatureCheckerInterface $featureChecker)
    {
        $this->featureChecker = $featureChecker;
    }

    public function index(): Response
    {
        if (!$this->featureChecker->isEnabled('new_checkout')) {
            throw new NotFoundHttpException();
        }
        // ...
    }
}
```

## 🧩 Providers

Providers are responsible for returning the feature callables. You can create your own provider by implementing the
`ProviderInterface`.

Any service that implements `ProviderInterface` is automatically registered as a provider. The bundle comes with a
`ChainProvider` that allows you to combine multiple providers. The first provider that returns a feature wins.

### The ProviderInterface

To create your own provider, you need to implement the `ProviderInterface`.

```php
namespace App\FeatureProvider;

use Ajgarlag\FeatureFlagBundle\Provider\ProviderInterface;

class MyProvider implements ProviderInterface
{
    public function get(string $featureName): ?callable
    {
        // ...
    }
}
```

The `get` method must return a `callable` if the provider has the feature, or `null` otherwise.

<details>
<summary>Doctrine example</summary>

```php
namespace App\FeatureProvider;

use Ajgarlag\FeatureFlagBundle\Provider\ProviderInterface;
use App\Repository\FeatureAssignmentRepository;

final class DoctrineProvider implements ProviderInterface
{
    private $featureAssignmentRepository;

    public function __construct(FeatureAssignmentRepository $featureAssignmentRepository)
    {
        $this->featureAssignmentRepository = $featureAssignmentRepository;
    }

    public function get(string $featureName): ?callable
    {
        // Set context. Example: user identifier, IP, hostname, etc.
        $context = [];

        return function () use ($featureName, $context) {
            return $this->featureAssignmentRepository->featureIsEnabled($featureName, $context);
        };
    }
}
```

</details>

<details>
<summary>Gitlab example</summary>

First, declare a service to interact with the [Unleash](https://www.getunleash.io/) API.

```yaml
# config/services.yaml
services:
    # A PSR-16 cache wrapping the "cache.unleash" pool, recommended to limit API calls.
    gitlab.unleash_cache:
        class: Symfony\Component\Cache\Psr16Cache
        arguments: ['@cache.unleash']

    gitlab.client_factory:
        class: Unleash\Client\UnleashBuilder
        factory: ['Unleash\Client\UnleashBuilder', 'createForGitlab']
        calls:
            - { method: withGitlabEnvironment, arguments: ['%env(GITLAB_ENVIRONMENT)%'], returns_clone: true }
            - { method: withAppUrl, arguments: ['%env(GITLAB_URL)%'], returns_clone: true }
            - { method: withInstanceId, arguments: ['%env(GITLAB_INSTANCE_ID)%'], returns_clone: true }
            - { method: withHttpClient, arguments: ['@psr18.http_client'], returns_clone: true }
            - { method: withCacheHandler, arguments: ['@gitlab.unleash_cache'], returns_clone: true }

    gitlab.client:
        class: Unleash\Client\Unleash
        factory: ['@gitlab.client_factory', 'build']

    App\FeatureProvider\GitlabProvider:
        arguments:
            $unleash: '@gitlab.client'
```

```php
namespace App\FeatureProvider;

use Ajgarlag\FeatureFlagBundle\Provider\ProviderInterface;
use Symfony\Component\Security\Core\Security;
use Unleash\Client\Configuration\UnleashContext;
use Unleash\Client\Unleash;

class GitlabProvider implements ProviderInterface
{
    private $unleash;
    private $security;

    public function __construct(Unleash $unleash, Security $security)
    {
        $this->unleash = $unleash;
        $this->security = $security;
    }

    public function get(string $featureName): ?callable
    {
        $user = $this->security->getUser();

        // Set context. Example: user identifier, IP, hostname, etc.
        $context = new UnleashContext($user ? $user->getUsername() : null);

        return function () use ($featureName, $context) {
            return $this->unleash->isEnabled($featureName, $context);
        };
    }
}
```

</details>

### Priority

You can control the order of the providers in the chain using the `priority` attribute of the
`ajgarlag.feature_flag.provider` tag.

Set the priority on the service tag in your configuration:

```yaml
# config/services.yaml
services:
    App\FeatureProvider\MyProvider:
        tags:
            - { name: 'ajgarlag.feature_flag.provider', priority: 10 }
```

Providers with a higher priority will be checked first.

## 🎨 Twig extension

The bundle provides two functions to use in Twig templates: `feature_is_enabled` and `feature_get_value`.

```twig
{% if feature_is_enabled('some_feature') %}
    {# ... #}
{% endif %}

{% if feature_get_value('some_feature') == 'some_value' %}
    {# ... #}
{% endif %}
```
