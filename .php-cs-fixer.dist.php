<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()->in([
    __DIR__ . '/src'
])
    ->name('*.php')
;

$rules = [
    '@PSR12' => true,
    'array_indentation' => true,
    'ordered_imports' => true,
    'no_unused_imports' => true,
    'array_syntax' => ['syntax' => 'short'],
];

$config = new Config();

return $config->setFinder($finder)->setRules($rules);
