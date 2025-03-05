<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Http\Requests\PaymentRequest;
use App\Services\UserService;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService,UserService $userService)
    {
        $this->paymentService = $paymentService;
        $this->userService = $userService;
    }

    public function initiatePaymnet(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json(['message' => 'Token not provided'], 400);
            }

            $user = $this->userService->getAuthenticatedUser($token);

            if (!$user) {
                return response()->json(['message' => 'Authentication failed or user not found'], 401);
            }

            $id         = random_int(1, 1000000000);
            $idAsString = $id . '';

            $payload = [
                'id'                 => $idAsString,
                'amount'             => 1,              //$request->amount,
                'reason'             =>"Refund for coffee", //$request->reason,
                'merchantId'         => '9e2dab64-e2bb-4837-9b85-d855dd878d2b', //$request->merchantId,
                'successRedirectUrl' => 'https://santimpay.com', //$request->successRedirectUrl,
                'failureRedirectUrl' => 'https://santimpay.com',  //$request->failureRedirectUrl,
                'notifyUrl'          => 'https://webhook.site/f0aeb478-edeb-4ebd-9bde-0334e60b84bb',
                'cancelRedirectUrl'  => 'https://santimpay.com', //$request->cancelRedirectUrl,
                'phoneNumber'        => '+251909126324',   //$request->phoneNumber ?? '',
            ];

            $paymentUrl = $this->paymentService->generatePaymentURL($payload);

            if (!$paymentUrl) {
                return response()->json(['message' => 'Error generating payment URL'], 500);
            }

            return response()->json(['paymentUrl' => $paymentUrl], 200);
        } catch (\Throwable $th) {
            \Log::error('Error initiating payment', ['error' => $th->getMessage()]);
            return response()->json(['message' => 'An error occurred while initiating the payment'], 500);
        }
    }
}
