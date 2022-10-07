<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PHP81Migration' => true,
        '@PHP80Migration:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,

        'array_indentation' => true,
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'control_structure_braces' => true,
        'date_time_immutable' => true,
        'declare_parentheses' => true,
        'declare_strict_types' => true,
        'group_import' => true,
        'mb_str_functions' => true,
        'no_multiple_statements_per_line' => true,
        'no_trailing_comma_in_singleline' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'ordered_class_elements' => true,
        'phpdoc_to_comment' => false,
        'phpdoc_to_param_type' => true,
        'phpdoc_to_property_type' => true,
        'phpdoc_to_return_type' => true,
        'phpdoc_var_annotation_correct_order' => true,
        'random_api_migration' => false,
        'single_import_per_statement' => false,
        'statement_indentation' => true,
        'static_lambda' => true,
        'strict_param' => true,
        'ternary_to_null_coalescing' => true,
        'use_arrow_functions' => false,
        'void_return' => true,

        'blank_line_before_statement' => [
            'statements' => [
                'declare',
                'include',
                'include_once',
                'require',
                'require_once',
                'return',
                'throw',
                'try',
                'yield',
            ],
        ],
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],
        'increment_style' => [
            'style' => 'post',
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'control_structure_continuation_position' => [
            'position' => 'same_line',
        ],
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => false,
            'after_heredoc' => true,
        ],
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'no_multi_line',
        ],
        'global_namespace_import' => [
            'import_classes' => false,
            'import_constants' => false,
            'import_functions' => false
        ],
        'trailing_comma_in_multiline' => [
            'after_heredoc' => true,
            'elements' => ['arrays'],
        ],
    ])
    ->setFinder($finder);
