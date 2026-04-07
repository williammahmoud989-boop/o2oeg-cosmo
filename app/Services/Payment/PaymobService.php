<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymobService
{
    protected $baseUrl = 'https://accept.paymob.com/api';
    protected $apiKey;
    protected $hmacSecret;
    protected $cardIntegrationId;
    protected $iframeId;

    public function __construct()
    {
        // Load defaults from config if available
        $this->apiKey = config('services.paymob.api_key');
        $this->hmacSecret = config('services.paymob.hmac_secret');
        $this->cardIntegrationId = config('services.paymob.card_integration_id');
        $this->iframeId = config('services.paymob.iframe_id');
    }

    /**
     * Initialize the service with salon-specific credentials.
     */
    public function initializeForSalon($salon)
    {
        if ($salon->paymob_api_key) {
            $this->apiKey = $salon->paymob_api_key;
        }
        if ($salon->paymob_hmac_secret) {
            $this->hmacSecret = $salon->paymob_hmac_secret;
        }
        if ($salon->paymob_card_integration_id) {
            $this->cardIntegrationId = $salon->paymob_card_integration_id;
        }
        if ($salon->paymob_iframe_id) {
            $this->iframeId = $salon->paymob_iframe_id;
        }

        return $this;
    }

    /**
     * Step 1: Authenticate with Paymob and get a token.
     */
    public function authenticate()
    {
        try {
            $response = Http::post("{$this->baseUrl}/auth/tokens", [
                'api_key' => $this->apiKey,
            ]);

            return $response->json('token');
        } catch (\Exception $e) {
            Log::error("Paymob Authentication Failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Step 2: Register an order on Paymob.
     */
    public function registerOrder($token, $booking, $items = [])
    {
        try {
            $response = Http::post("{$this->baseUrl}/ecommerce/orders", [
                'auth_token' => $token,
                'delivery_needed' => 'false',
                'amount_cents' => (int) ($booking->total_price * 100),
                'currency' => 'EGP',
                'merchant_order_id' => $booking->booking_code . '_' . time(),
                'items' => $items,
            ]);

            return $response->json('id');
        } catch (\Exception $e) {
            Log::error("Paymob Order Registration Failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Step 3: Generate a payment key for the iframe.
     */
    public function generatePaymentKey($token, $orderId, $booking, $user)
    {
        try {
            $nameParts = explode(' ', $user->name);
            $firstName = $nameParts[0] ?? 'Customer';
            $lastName = $nameParts[1] ?? 'Name';

            $response = Http::post("{$this->baseUrl}/acceptance/payment_keys", [
                'auth_token' => $token,
                'amount_cents' => (int) ($booking->total_price * 100),
                'expiration' => 3600,
                'order_id' => $orderId,
                'billing_data' => [
                    'apartment' => 'NA',
                    'email' => $user->email,
                    'floor' => 'NA',
                    'first_name' => $firstName,
                    'street' => 'NA',
                    'building' => 'NA',
                    'phone_number' => $user->phone ?? '01000000000',
                    'shipping_method' => 'NA',
                    'postal_code' => 'NA',
                    'city' => 'NA',
                    'country' => 'EG',
                    'last_name' => $lastName,
                    'state' => 'NA',
                ],
                'currency' => 'EGP',
                'integration_id' => $this->cardIntegrationId,
                'lock_order_when_paid' => 'false',
            ]);

            return $response->json('token');
        } catch (\Exception $e) {
            Log::error("Paymob Payment Key Generation Failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the full URL for the Paymob iframe.
     */
    public function getIframeUrl($paymentKeyToken)
    {
        return "https://accept.paymob.com/api/acceptance/iframes/{$this->iframeId}?payment_token={$paymentKeyToken}";
    }

    /**
     * Verify Paymob HMAC signature.
     */
    public function verifyHmac(array $data, string $hmac)
    {
        $keys = [
            'amount_cents',
            'created_at',
            'currency',
            'error_occured',
            'has_parent_transaction',
            'id',
            'integration_id',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'is_refunded',
            'is_standalone_payment',
            'is_voided',
            'order_id',
            'owner',
            'pending',
            'source_data_pan',
            'source_data_sub_type',
            'source_data_type',
            'success',
        ];

        $concatenatedString = '';
        foreach ($keys as $key) {
            $val = $data[$key] ?? '';
            // Booleans should be stringified
            if (is_bool($val)) {
                $val = $val ? 'true' : 'false';
            }
            $concatenatedString .= $val;
        }

        $calculatedHmac = hash_hmac('sha512', $concatenatedString, $this->hmacSecret);
        
        return hash_equals($hmac, $calculatedHmac);
    }
}
