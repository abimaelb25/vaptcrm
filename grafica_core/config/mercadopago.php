<?php

declare(strict_types=1);

return [
    'access_token' => env('MERCADOPAGO_ACCESS_TOKEN', ''),
    'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET', ''),
    'public_key' => env('MERCADOPAGO_PUBLIC_KEY', ''),
];
