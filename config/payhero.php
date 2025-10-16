<?php

return [
    // API credentials (stored in env)
    'username' => env('PAYHERO_USERNAME'),
    'api_key' => env('PAYHERO_API_KEY'),

    // Default channel and provider
    'channel_id' => env('PAYHERO_CHANNEL_ID'),
    'provider' => env('PAYHERO_PROVIDER', 'm-pesa'),

    // Webhook secret for verification
    'webhook_secret' => env('PAYHERO_WEBHOOK_SECRET'),

    // Webhook signature settings
    // PayHero may send signatures in different formats. We support a raw signature header
    // or a timestamped signature like: "t=163...,v1=<signature>" (Stripe-style).
    'signature_header' => env('PAYHERO_SIGNATURE_HEADER', 'X-Payhero-Signature'),
    'timestamp_header' => env('PAYHERO_TIMESTAMP_HEADER', 'X-Payhero-Timestamp'),
    'webhook_ttl' => env('PAYHERO_WEBHOOK_TTL', 300), // seconds

    // PayHero API endpoint
    'endpoint' => env('PAYHERO_ENDPOINT', 'https://backend.payhero.co.ke/api/v2'),
];
