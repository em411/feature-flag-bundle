#!/usr/bin/env php
<?php

declare(strict_types=1);

$newVersion = $argv[1] ?? '';

if ($newVersion === '') {
    throw new LogicException('Provide a Symfony version in Composer requirement format (e.g. "^7.0")');
}

$composerPath    = __DIR__ . '/../../composer.json';
$composerContent = file_get_contents($composerPath);

if ($composerContent === false) {
    throw new LogicException('Could not read composer.json file');
}

// Pin all symfony/* packages that follow the main Symfony release versioning,
// but skip symfony/service-contracts which uses its own independent version line (^1.x / ^2.x / ^3.x).
$updatedComposer = preg_replace('/"symfony\/(?!service-contracts)(.*)": ".*"/', '"symfony/$1": "' . $newVersion . '"', $composerContent);

if ($updatedComposer === null) {
    throw new LogicException('Failed to update composer.json content');
}

echo $updatedComposer . PHP_EOL;

if (file_put_contents($composerPath, $updatedComposer) === false) {
    throw new LogicException('Could not write to composer.json file');
}
