<?php

return [
    'postmark' => ['token' => env('POSTMARK_TOKEN')],
    'resend' => ['key' => env('RESEND_KEY')],
    'ses' => ['key' => env('AWS_ACCESS_KEY_ID'), 'secret' => env('AWS_SECRET_ACCESS_KEY'), 'region' => env('AWS_DEFAULT_REGION', 'us-east-1')],
    'slack' => ['notifications' => ['bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'), 'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL')]],
    'openai' => ['key' => env('OPENAI_API_KEY'), 'model' => env('OPENAI_CHAT_MODEL', 'gpt-5.6-luna')],
    'dtz' => [
        // Palaz Online runs on :8000 locally; DTZ must use a separate port.
        'url' => env('DTZ_URL', 'http://127.0.0.1:8001'),
        'palaz_token' => env('DTZ_PALAZ_TOKEN'),
    ],
    'openrouter' => [
        'key' => env('OPENROUTER_API_KEY'),
        'model' => env('OPENROUTER_MODEL', 'openrouter/free'),
        // Color analysis must use an explicitly vision-capable model, independently of the chat model.
        'vision_model' => env('OPENROUTER_VISION_MODEL', 'google/gemma-4-31b-it:free'),
    ],
    // Server-side Persian speech; does not depend on voices installed on a customer's device.
    'elevenlabs' => [
        'key' => env('ELEVENLABS_API_KEY'),
        'voice_id' => env('ELEVENLABS_VOICE_ID'),
        'model' => env('ELEVENLABS_TTS_MODEL', 'eleven_v3'),
    ],
];
