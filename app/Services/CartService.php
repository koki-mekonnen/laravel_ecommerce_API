<?php
namespace App\Services;

use App\Repositories\CartRepository;

class CartService
{
    protected $repository;

    public function __construct(CartRepository $repository)
    {
        $this->repository = $repository;
    }

    public function addtoCart(array $data)
    {
        \Log::info('data received in the cart', $data);

        try {

            $cart = $this->repository->addtocart($data);
           return $cart;

        } catch (\Exception $e) {

            \Log::error('Error registering product: ' . $e->getMessage());
            throw $e;
        }
    }


    public function findCartItem($productid, $ownerid){
        try {

            $cartItem = $this->repository->findCartItem($productid, $ownerid);
            if (! $cartItem) {
                return null;
            }
            return $cartItem;
        } catch (\Exception $e) {
            \Log::error('Error getting cart item: '. $e->getMessage());
            throw $e;
        }
    }




}
