<?php

return (new PhpCsFixer\Config())
    //~ Rules
    ->setRules(
        [
            '@PER-CS2.0' => true,
        ]
    )

    //~ Format
    ->setFormat('txt')

    //~ Cache
    ->setUsingCache(true)
    ->setCacheFile(__DIR__ . '/build/.php-cs-fixer.cache')

    //~ Finder
    ->setFinder((new PhpCsFixer\Finder())->in([__DIR__ . '/src', __DIR__ . '/tests']))
;
