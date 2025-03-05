<?php

namespace App\Services;

use App\Repositories\PaymentRepository;

class PaymentService
{
    protected $paymentRepository;

    public function __construct(PaymentRepository $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Generate a signed token for payment.
     *
     * @param float $amount
     * @param string $reason
     * @return string|null
     */
    public function generateSignedToken($amount, $reason)
    {
        return $this->paymentRepository->generateSignedToken($amount, $reason);
    }

    /**
     * Generate a payment URL.
     *
     * @param array $payload
     * @return string|null
     */
    public function generatePaymentURL(array $payload)
    {
        // Add the signed token to the payload
        $payload['signedToken'] = $this->generateSignedToken($payload['amount'], $payload['reason']);

        if (!$payload['signedToken']) {
            \Log::error('Signed token generation failed.');
            return null;
        }

        // Call the repository to generate the payment URL
        return $this->paymentRepository->generatePaymentURL($payload);
    }
}
