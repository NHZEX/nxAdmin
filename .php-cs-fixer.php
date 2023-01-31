<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        'app',
        'extend',
    ])
    ->notName('auth_storage.php')
    ->notName('route_storage.dump.php')
    ->notName('validate_storage.php');
$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@PSR12'                     => true,
        '@PHP74Migration'            => true,
        'normalize_index_brace'      => true,
        'global_namespace_import'    => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
        'operator_linebreak'         => ['only_booleans' => true, 'position' => 'beginning'],
        'standardize_not_equals'     => true,
        'unary_operator_spaces'      => true,
        // risky
        'native_function_invocation' => ['include' => ['@compiler_optimized'], 'scope' => 'all', 'strict' => true],
        'function_to_constant'       => true,
        // 暂时不要发生过大的变动范围
        'blank_line_between_import_groups' => false,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
