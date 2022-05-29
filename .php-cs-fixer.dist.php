<?php

$finder = (new PhpCsFixer\Finder())
    ->in(
        [
            __DIR__ . '/src',
        ]
    );
$finder->exclude(
    [
        __DIR__ . '/tests',
    ]
);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'braces' => ['allow_single_line_closure' => true],
        'compact_nullable_typehint' => true,
        'concat_space' => ['spacing' => 'one'],
        'declare_equal_normalize' => ['space' => 'none'],
        'function_typehint_space' => true,
        'new_with_braces' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'no_empty_statement' => true,
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_whitespace_in_blank_line' => true,
        'return_type_declaration' => ['space_before' => 'none'],
        'single_trait_insert_per_statement' => true,
        'yoda_style' => false,
    ])
    ->setFinder($finder);
