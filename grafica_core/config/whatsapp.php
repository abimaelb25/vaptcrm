<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Business Platform Configuration
    |--------------------------------------------------------------------------
    |
    | Only the official Meta Cloud API is supported.
    | Never use unofficial libraries, QR scraping, or mirrored sessions.
    |
    */

    'meta' => [
        /**
         * Meta App Secret — used to verify X-Hub-Signature-256 on webhooks.
         * Required in production. If empty and APP_ENV=local, signature check is skipped.
         */
        'app_secret' => env('WHATSAPP_APP_SECRET'),

        /**
         * Meta App ID — used for embedded signup SDK initialisation.
         */
        'app_id' => env('WHATSAPP_APP_ID'),

        /**
         * Cloud API version. Update when Meta deprecates older versions.
         */
        'api_version' => env('WHATSAPP_API_VERSION', 'v19.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    */

    'queue' => env('WHATSAPP_QUEUE', 'whatsapp'),

    /*
    |--------------------------------------------------------------------------
    | AI Automation Layer (future)
    |--------------------------------------------------------------------------
    |
    | Set enabled=true when ready to activate AI suggestion layer.
    | The structure for conversation context is already built into the
    | WhatsAppMessage model (is_automated flag + template_data).
    |
    */

    'ai' => [
        'enabled'       => env('WHATSAPP_AI_ENABLED', false),
        'provider'      => env('WHATSAPP_AI_PROVIDER', null),  // e.g. openai
        'fallback_human' => true,  // always fall back to human when AI fails
    ],

];
