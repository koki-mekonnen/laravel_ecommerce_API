<?php

namespace App\Repositories;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;

class PaymentRepository
{
    protected $merchantId;
    protected $privateKey;
    protected $paymentGatewayUrl;
    protected $httpClient;

    public function __construct()
    {
        $this->merchantId = env('PAYMENT_MERCHANT_ID');
        $this->privateKey ="-----BEGIN EC PRIVATE KEY-----
MHcCAQEEILd62Ot4hMSxIwO1TRZQnAD02FHV5Hxvc7PbHU3nWQWwoAoGCCqGSM49
AwEHoUQDQgAEBy11auaVru1GUhPavhEub2tfx8P6EvdVnq+BL/fDEe83IwgOrkvg
bTa6BEUQkKoqsn+8bpJ3BIYAI2Iqa+KzZw==
-----END EC PRIVATE KEY-----
";
  $this->paymentGatewayUrl = env('PAYMENT_GATEWAY_URL');
        $this->httpClient = new Client();
    }

    /**
     * Generate a signed JWT token.
     *
     * @param float $amount
     * @param string $paymentReason
     * @return string|null
     */
    public function generateSignedToken($amount, $paymentReason)
    {
        try {
            $time = time();
            $data = [
                'amount'        => $amount,
                'paymentReason' => $paymentReason,
                'merchantId'    => $this->merchantId,
                'generated'     => $time,
            ];

            // Generate JWT token
            $jwt = JWT::encode($data, $this->privateKey, 'ES256');

        \Log::info("token generated",[$jwt]);

            return $jwt;
        } catch (\Exception $e) {
            \Log::error('Error generating signed token', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Generate a payment URL by calling the payment gateway's API.
     *
     * @param array $body
     * @return string|null
     */
    public function generatePaymentURL(array $body)
    {
        try {
            $headers = [
                'Content-Type' => 'application/json; charset=UTF-8',
            ];

            // Make the POST request to the payment gateway
            $response = $this->httpClient->post($this->paymentGatewayUrl . '/initiate-payment', [
                'headers' => $headers,
                'body'    => json_encode($body),
                'verify'  => false,
            ]);

            $responseContent = $response->getBody()->getContents();

            // Replace "\u0026" with "&" (if needed in the response)
            $responseContent = str_replace('\u0026', '&', $responseContent);

            return $responseContent;
        } catch (\Exception $e) {
            \Log::error('Error generating payment URL', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
