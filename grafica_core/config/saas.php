<?php

declare(strict_types=1);

return [
    'grace_period_days' => (int) env('SAAS_GRACE_PERIOD_DAYS', 3),

    'trial_default_days' => (int) env('SAAS_TRIAL_DEFAULT_DAYS', 14),

    'billing' => [
        'default_cycle' => env('SAAS_BILLING_DEFAULT_CYCLE', 'monthly'),
        'allow_downgrade_with_overage' => (bool) env('SAAS_ALLOW_DOWNGRADE_WITH_OVERAGE', true),
    ],

    'cache' => [
        'store' => env('SAAS_CACHE_STORE', 'redis'),
        'subscription_ttl_seconds' => (int) env('SAAS_CACHE_SUBSCRIPTION_TTL', 180),
        'feature_ttl_seconds' => (int) env('SAAS_CACHE_FEATURE_TTL', 180),
        'limit_ttl_seconds' => (int) env('SAAS_CACHE_LIMIT_TTL', 180),
    ],

    'storage' => [
        'warn_threshold_percent' => (int) env('SAAS_STORAGE_WARN_THRESHOLD', 80),
        'critical_threshold_percent' => (int) env('SAAS_STORAGE_CRITICAL_THRESHOLD', 95),
        'hard_block_threshold_percent' => (int) env('SAAS_STORAGE_HARD_BLOCK_THRESHOLD', 100),
        'soft_block_min_upload_bytes' => (int) env('SAAS_STORAGE_SOFT_BLOCK_MIN_UPLOAD_BYTES', 5242880),
    ],

    'usage_pricing' => [
        'currency' => env('SAAS_USAGE_CURRENCY', 'BRL'),
        'metrics' => [
            'max_pedidos_mes' => [
                'unit_price' => (float) env('SAAS_PRICE_PEDIDO_EXCEDENTE', 0),
            ],
            'max_storage_mb' => [
                'unit_price' => (float) env('SAAS_PRICE_STORAGE_MB_EXCEDENTE', 0),
            ],
            'max_produtos' => [
                'unit_price' => (float) env('SAAS_PRICE_PRODUTO_EXCEDENTE', 0),
            ],
            'max_usuarios' => [
                'unit_price' => (float) env('SAAS_PRICE_USUARIO_EXCEDENTE', 0),
            ],
            'max_ops_simultaneas' => [
                'unit_price' => (float) env('SAAS_PRICE_OP_EXCEDENTE', 0),
            ],
            'max_producao_ativa' => [
                'unit_price' => (float) env('SAAS_PRICE_PRODUCAO_ATIVA_EXCEDENTE', 0),
            ],
        ],
    ],
];
