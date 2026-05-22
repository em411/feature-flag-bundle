<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('tests/bin')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP74Migration' => true,
        'header_comment' => ['header' => ''],
    ])
    ->setFinder($finder)
;
