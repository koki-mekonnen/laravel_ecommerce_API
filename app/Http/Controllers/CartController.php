<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Services\CategoryService;
use App\Services\CartService;
use App\Services\MerchantService;

class CartController extends Controller
{
    protected $merchantService;
    protected $productService;
    protected $categoryService;
    protected $cartService;

    public function __construct(CartService $cartService, MerchantService $merchantService, CategoryService $categoryService,ProductService $productService)
    {
        $this->merchantService = $merchantService;
        $this->productService  = $productService;
        $this->categoryService = $categoryService;
        $this->cartService = $cartService;
    }

    public function addtocart(Request $request, $productid)
     {

        try {
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Fetch the product details
        $product = $this->productService->getById($productid);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        \Log::info('Product found: ' . json_encode($product));


        // Check if the product is already in the cart
        $cartItem = $this->cartService->findCartItem($product->id, $product->owner_id);



        if ($cartItem) {

       if (($cartItem->amount + $data['quantity']) > $product->product_quantity) {
       return response()->json(['message' => 'Requested quantity exceeds available stock'], 400);
      }

            $cartItem->amount += $data['quantity'];
            $cartItem->totalprice = $cartItem->product_price * $cartItem->amount;
            $cartItem->save();

            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully',
                'data'    => $cartItem,
            ], 200);
        }

         if ($data['quantity'] > $product->product_quantity) {
            return response()->json(['message' => 'Requested quantity exceeds available stock'], 400);
        }

        // Prepare cart data
        $cartData = [
            'product_id'          => $product->id,
            'product_name'        => $product->product_name,
            'product_description' => $product->product_description,
            'product_price'       => $product->product_price,
            'discount'            => $product->discount,
            'product_size'        => $product->product_size,
            'product_color'       => $product->product_color,
            'product_image'       => $product->product_image,
            'product_brand'       => $product->product_brand,
            'product_quantity'    => $data['quantity'],
            'owner_id'            => $product->owner_id,
            'category_id'         => $product->category_id,
            'category_name'       => $product->category_name,
            'amount'              => $data['quantity'],
            'totalprice'          => $product->price * $data['quantity'],
            'status'              => 'active',

        ];

        // Add to cart using CartService
        $cartItem = $this->cartService->addtoCart($cartData);

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully',
            'data'    => $cartItem,
        ], 201);


    } catch (\Exception  $e) {
        \Log::error('Error during add to cart: ' . $e->getMessage());
        return response()->json([
            'error'   => 'An unexpected error occurred',
            'message' => $e->getMessage(),
        ], 500);
    }
}

}
