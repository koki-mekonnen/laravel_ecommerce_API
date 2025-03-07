<?php
namespace App\Http\Controllers;

use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    public function handleWebhook(Request $request)
    {
        try {
            $signature = $request->header('signed-token');
            \Log::info("signnature',[$signature");

            $isValid = $this->webhookService->validateWebhookToken($signature);
            \Log::info("isValid", [$isValid]);

            if (! $isValid) {
                // Process the webhook data
                $status = $request->Status;
                \Log::info("status", [$request->Status]);

                if ($status === 'COMPLETED') {
                    $transaction = $this->webhookService->transactions($request->all());

                    if (! $transaction) {
                        Log::error('Transaction processing failed.', ['request' => $request->all()]);
                        return response()->json(['message' => 'Failed to send transaction data'], 500);
                    }

                    return response()->json($transaction, 200);
                } elseif ($status === 'FAILED') {
                    Log::warning('Payment failed.', ['request' => $request->all()]);
                    return response()->json(['message' => 'Payment failed, please try again'], 401);
                } else {
                    Log::warning('Unrecognized payment status.', ['status' => $status, 'request' => $request->all()]);
                    return response()->json(['message' => 'Payment status not recognized'], 400);
                }
            } else {

                Log::error('Invalid webhook token.', ['signature' => $signature, 'request' => $request->all()]);
                return response()->json(['message' => 'The token is not from the correct issuer'], 200);
            }
        } catch (\Exception $e) {
            Log::error('Webhook handling error.', ['error' => $e->getMessage(), 'request' => $request->all()]);
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }
}
