<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules(
        [
            '@PHP84Migration' => true,
            '@PHP80Migration:risky' => true,
            '@PHPUnit100Migration:risky' => true,
            '@Symfony' => true,
            '@Symfony:risky' => true,
            '@PhpCsFixer' => true,
            '@PhpCsFixer:risky' => true,
            'declare_strict_types' => true,
            'random_api_migration' => true,
            'dir_constant' => true,
            'modernize_types_casting' => true,
            'php_unit_construct' => true,
            'psr_autoloading' => true,
            'final_internal_class' => true,
            'php_unit_strict' => [
                'assertions' => [
                    'assertNotEquals',
                ],
            ],
            'php_unit_test_case_static_method_calls' => true,
            'strict_comparison' => true,
            'strict_param' => true,
            'date_time_immutable' => true,
            'general_phpdoc_annotation_remove' => true,
            'mb_str_functions' => true,
            'multiline_whitespace_before_semicolons' => true,
            'no_php4_constructor' => true,
            'no_superfluous_phpdoc_tags' => [
                'allow_mixed' => true,
            ],
            'not_operator_with_space' => false,
            'not_operator_with_successor_space' => false,
            'ordered_interfaces' => true,
            'php_unit_size_class' => true,
            'phpdoc_to_return_type' => true,
            'static_lambda' => true,
            'phpdoc_to_comment' => false, // This allows us some flexibility with PhpStan type resolution
            'concat_space' => [
                'spacing' => 'one',
            ],
        ]
    )
    ->setFinder($finder);
