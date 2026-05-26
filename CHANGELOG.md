# Changelog

All notable changes to `em411/feature-flag-bundle` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.5] - 2026-05-26

### Changed
- CI: bumped `actions/checkout` v4 → v6 and `actions/cache` v4 → v5 to run on
  Node.js 24. GitHub deprecated Node.js 20 for Actions; the older versions
  emitted deprecation warnings on every run and will stop working in
  September 2026. No effect on the published package.

## [1.0.4] - 2026-05-26

### Fixed
- CI: composer dependency resolution failed against the latest packages because
  Symfony 4.4 and Twig 2.x carry security advisories that newer Composer
  versions now block by default. Set `config.audit.block-insecure: false` —
  Symfony 4.4 is EOL upstream and users on this backport already accept the
  EOL security posture. Advisories are still reported on install, just not
  blocking.
- CI: `twig/twig 3.26.0` was released with a PHP 8.1+ requirement, breaking the
  PHP 8.0 leg of the matrix. Capped the require-dev constraint to
  `^2.12 | >=3.0,<3.7` so CI picks a version compatible with both PHP 7.4 and
  PHP 8.0. End users are unaffected (Twig is not in `require`).

## [1.0.3] - 2026-05-26

First Packagist release. Pre-launch polish on top of `1.0.2`.

### Added
- `CHANGELOG.md`.
- README badges (CI, Packagist version, license).
- README "Which package should I use?" section pointing modern stacks to the upstream `ajgarlag/feature-flag-bundle`.
- README note explaining why the PHP namespace stays `Ajgarlag\FeatureFlagBundle\` despite the `em411/*` package name.
- Explicit `Antonio J. García Lagar` copyright line in `LICENSE`; explicit `em411` maintainer entry.
- `composer.json` `authors` now lists upstream maintainer `ajgarlag` and the backport maintainer `em411` with `role` annotations.

### Changed
- Clarified the duplicate-feature error message in `FeatureFlagPass`: it no longer references the misnamed `ajgarlag.feature_flag.provider.in_memory` service (the service id is a leftover from upstream; it is actually backed by `ServiceProvider`).
- `composer.json` `minimum-stability`: `dev` → `stable` (no dev deps required it).

## [1.0.2] - 2026-05-22

### Fixed
- Twig profiler template: use Twig 2-compatible named-argument syntax for `profiler_dump` (PR #3).

## [1.0.1] - 2026-05-22

### Fixed
- Override `Bundle::getPath()` so the profiler template resolves correctly when the bundle is consumed via Composer (PR #2).

## [1.0.0] - 2026-05-22

Initial release of the Symfony 4.4 / PHP 7.4 backport of
[`ajgarlag/feature-flag-bundle`](https://github.com/ajgarlag/feature-flag-bundle).

### Added
- `FeatureChecker` / `FeatureCheckerInterface` services for checking whether a feature is enabled.
- `ProviderInterface` with built-in `ChainProvider` and `ServiceProvider`. Any service implementing `ProviderInterface` is autoconfigured as a provider.
- `@AsFeature` Doctrine annotation for declaring features on classes and methods (PHP 7.4 has no native attributes).
- Twig functions `feature_is_enabled()` and `feature_get_value()`.
- Web profiler data collector showing resolved feature values per request.

### Changed (vs. upstream `ajgarlag/feature-flag-bundle`)
- Targets Symfony 4.4 and PHP 7.4 / 8.0.
- Features are declared with the `@AsFeature` Doctrine annotation instead of the `#[AsFeature]` PHP 8 attribute. A single annotation per class or method is supported; use the `ajgarlag.feature_flag.feature` service tag to declare more than one feature on the same class.
- Routing integration removed — Symfony 4.4 cannot evaluate services inside route `condition` expressions. Use controller-level gating (see README).
- Service configuration moved from PHP to XML.

### Notes
- The PHP namespace is intentionally kept as `Ajgarlag\FeatureFlagBundle\` so code written against the upstream package works here unchanged.
