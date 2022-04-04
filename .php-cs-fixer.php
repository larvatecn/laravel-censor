<?php

$header = <<<'EOF'
This is NOT a freeware, use is subject to license terms.

@copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
EOF;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        '@DoctrineAnnotation' => true,
        'header_comment' => [
            'comment_type' => 'PHPDoc',
            'header' => $header,
            'separate' => 'bottom',
            'location' => 'after_open',
        ],
        'binary_operator_spaces' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'declare',
            ],
        ],
        'blank_line_after_opening_tag' => true,
        'compact_nullable_typehint' => true,
        'declare_equal_normalize' => true,
        'lowercase_cast' => true,
        'lowercase_static_reference' => true,
        'new_with_braces' => true,
        'no_unused_imports' => false,
        'no_blank_lines_after_class_opening' => true,
        'no_leading_import_slash' => true,
        'no_whitespace_in_blank_line' => true,
        'single_trait_insert_per_statement' => false,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
            ],
        ],
        'ordered_imports' => [
            'imports_order' => [
                'class', 'function', 'const',
            ],
            'sort_algorithm' => 'none',
        ],
        'list_syntax' => [
            'syntax' => 'short'
        ],
        'phpdoc_align' => [
            'align' => 'left',
        ],
        'constant_case' => [
            'case' => 'lower',
        ],
        'return_type_declaration' => true,
        'short_scalar_cast' => true,
        'single_blank_line_before_namespace' => true,
        'ternary_operator_spaces' => true,
        'unary_operator_spaces' => true,
        'visibility_required' => [
            'elements' => [
                'const',
                'method',
                'property',
            ],
        ],
        'combine_consecutive_unsets' => true,
        'linebreak_after_opening_tag' => true,
        'phpdoc_separation' => false,
        'single_quote' => true,
        'standardize_not_equals' => true,
        'multiline_comment_opening_closing' => true,
        'ternary_to_null_coalescing' => true,
        //'declare_strict_types' => true,//激进，强制打开严格模式
    ])->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('bootstrap')
            ->exclude('config')
            ->exclude('database')
            ->exclude('lang')
            ->exclude('node_modules')
            ->exclude('public')
            ->exclude('storage')
            ->exclude('vendor')
            ->exclude('resources')
            ->exclude('routes')
            ->in(__DIR__)
    )->setUsingCache(false);
