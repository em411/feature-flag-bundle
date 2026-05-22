UPGRADE FROM 0.2 to 0.3
=======================

* `ProviderInterface::get` return type has changed from `?\Closure` to `?callable`.

Symfony 4.4 / PHP 7.4 backport
==============================

This branch is a backport of the `0.3.x` line to Symfony 4.4 and PHP 7.4.

* Features are declared with the `@AsFeature` **Doctrine annotation** instead of
  the `#[AsFeature]` PHP 8 attribute. A single annotation per class or method is
  supported; use the `ajgarlag.feature_flag.feature` service tag to declare more
  than one feature on the same class.
* The **routing integration was removed** — Symfony 4.4 has no condition
  services and cannot evaluate feature checks inside route conditions. See the
  README for a controller-level alternative.
* The Twig functions and the web profiler panel are unchanged.
