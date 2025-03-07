<?php
namespace App\Services;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Repositories\WebhookRepository;


class WebhookService
{
 protected $repository;

    public function __construct(WebhookRepository $repository)
    {
        $this->repository = $repository;
        // $this->merchantRepository = new MerchantRepository();



    }

    public function validateWebhookToken($token){
        try {
            $valid=$this->repository->validateWebhookToken($token);

            if (!$valid) {
                return false;
            }
            return $valid;
        } catch (JWTException $e) {
            return false;
        }
    }


    public function transactions(array $data){
        try {
            $transactions = $this->repository->handlewebhook($data);
            return $transactions;
        } catch (\Exception $e) {
            \Log::error('Error getting transactions: '. $e->getMessage());
            throw $e;
        }
    }
}
