<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

class PayHeroService
{
    protected $username;
    protected $apiKey;
    protected $endpoint;
    protected $channelId;
    protected $provider;
    protected $webhookSecret;
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->username = config('payhero.username');
        $this->apiKey = config('payhero.api_key');
        $this->endpoint = rtrim(config('payhero.endpoint'), '/');
        $this->channelId = config('payhero.channel_id');
        $this->provider = config('payhero.provider');
        $this->webhookSecret = config('payhero.webhook_secret');
        $this->logger = $logger;
    }

    /**
     * Initiate a payment (STK Push) with PayHero.
     * Returns the Http client response object.
     */
    public function initiatePayment(array $data)
    {
        $url = $this->endpoint . '/payments';

        $payload = [
            'amount' => $data['amount'],
            'phone_number' => $data['phone_number'],
            'channel_id' => $data['channel_id'] ?? $this->channelId,
            'provider' => $data['provider'] ?? $this->provider,
            'external_reference' => $data['external_reference'] ?? 'PH_'.Str::random(12),
            'callback_url' => $data['callback_url'] ?? url('/api/webhooks/payhero'),
        ];

        $this->logger->info('PayHeroService:initiatePayment - sending payload', ['payload' => $payload]);

        try {
            $response = Http::withBasicAuth($this->username, $this->apiKey)
                        ->acceptJson()
                        ->post($url, $payload);

            $this->logger->info('PayHeroService:initiatePayment - response', ['status' => $response->status(), 'body' => $response->body()]);

            return $response;
        } catch (\Throwable $ex) {
            $this->logger->error('PayHeroService:initiatePayment - exception', ['message' => $ex->getMessage()]);
            throw $ex;
        }
    }

    /**
     * Initiate a payout via PayHero (Payouts API)
     */
    public function initiatePayout(array $data)
    {
        $url = $this->endpoint . '/payouts';

        $payload = [
            'amount' => $data['amount'],
            'destination' => $data['destination'] ?? null,
            'external_reference' => $data['external_reference'] ?? 'PO_'.Str::random(12),
            'metadata' => $data['metadata'] ?? [],
        ];

        $this->logger->info('PayHeroService:initiatePayout - sending payload', ['payload' => $payload]);

        $response = Http::withBasicAuth($this->username, $this->apiKey)
                    ->acceptJson()
                    ->post($url, $payload);

        $this->logger->info('PayHeroService:initiatePayout - response', ['status' => $response->status(), 'body' => $response->body()]);

        return $response;
    }

    /**
     * Verify webhook signature using the configured webhook secret.
     * Returns boolean true if valid.
     */
    public function verifyWebhook(Request $request): bool
    {
        // If no webhook secret is configured, accept webhooks explicitly.
        // In production it's recommended to set a webhook secret and verify signatures.
        if (! $this->webhookSecret) {
            $this->logger->info('PayHeroService:verifyWebhook - no webhook secret configured; accepting webhook by default');
            return true;
        }

        // PayHero's webhook signature mechanism may vary; this is a placeholder.
        // Expecting header 'X-Payhero-Signature' with HMAC-SHA256 of the raw body.
        $headerName = config('payhero.signature_header', 'X-Payhero-Signature');
        $timestampHeader = config('payhero.timestamp_header', 'X-Payhero-Timestamp');

        $signatureHeader = $request->header($headerName) ?? $request->header('X-Signature');
        if (! $signatureHeader) {
            $this->logger->warning('PayHeroService:verifyWebhook - signature header missing');
            return false;
        }

        $body = (string) $request->getContent();

        // Support two formats: raw signature or timestamped signature like "t=...,v1=<sig>"
        if (strpos($signatureHeader, 't=') !== false && strpos($signatureHeader, 'v1=') !== false) {
            // parse timestamped signature
            $parts = explode(',', $signatureHeader);
            $t = null;
            $v1 = null;
            foreach ($parts as $part) {
                [$k, $v] = explode('=', $part, 2) + [null, null];
                if ($k === 't') $t = $v;
                if ($k === 'v1') $v1 = $v;
            }

            if (! $t || ! $v1) {
                $this->logger->warning('PayHeroService:verifyWebhook - timestamped signature malformed', ['header' => $signatureHeader]);
                return false;
            }

            // check TTL
            $ttl = config('payhero.webhook_ttl', 300);
            if (abs(time() - (int) $t) > $ttl) {
                $this->logger->warning('PayHeroService:verifyWebhook - signature timestamp outside TTL', ['timestamp' => $t, 'ttl' => $ttl]);
                return false;
            }

            $calculated = hash_hmac('sha256', $t . '.' . $body, $this->webhookSecret);
            $valid = hash_equals($calculated, $v1);
            if (! $valid) {
                $this->logger->warning('PayHeroService:verifyWebhook - signature mismatch (v1)', ['calculated' => $calculated, 'v1' => $v1]);
            }
            return $valid;
        }

        // fallback: raw HMAC of body
        $calculated = hash_hmac('sha256', $body, $this->webhookSecret);
        $valid = hash_equals($calculated, $signatureHeader);
        if (! $valid) {
            $this->logger->warning('PayHeroService:verifyWebhook - signature mismatch (raw)', ['calculated' => $calculated, 'header' => $signatureHeader]);
        }

        return $valid;
    }
}
