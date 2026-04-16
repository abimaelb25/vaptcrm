<?php

declare(strict_types=1);

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 2026-04-07 22:32 -03:00
*/

return [
    // Chave pública (usada no frontend — segura para exposição)
    'key' => env('STRIPE_KEY', ''),

    // Chave secreta (NUNCA exposta ao frontend)
    'secret' => env('STRIPE_SECRET', ''),

    // Segredo do webhook (para validar assinatura HMAC dos eventos)
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),

    // Versão da API Stripe fixada para evitar quebras por upgrades automáticos
    'api_version' => '2024-06-20',

    // Moeda padrão (BRL — Real Brasileiro)
    'currency' => 'BRL',

    // Valor mínimo em centavos (R$ 0,50 = 50 centavos)
    'valor_minimo_centavos' => 50,

    // Métodos de pagamento habilitados no Checkout
    // PIX e Boleto requerem ativação no dashboard: https://dashboard.stripe.com/settings/payment_methods
    // Para ativar: acesse o link acima, localize Pix/Boleto e clique em Ativar
    'metodos_pagamento' => ['card'],
];
