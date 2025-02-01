<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        // Set default values for nullable fields
        $this->merge([
            'product_description' => $this->input('product_description', ''),
            'discount' => $this->input('discount', 0),
            'product_size' => $this->input('product_size', []),
            'product_color' => $this->input('product_color', []),
            'product_image' => $this->input('product_image', []),
            'product_brand' => $this->input('product_brand', ''),
        ]);
    }
    public function rules()
    {
        $routeName = $this->route()->getName();
        // $ownerId = auth()->user()->id; // Assuming the authenticated user is the owner

        // Validation rules for creating a product
        if ($routeName === 'merchant.createproduct') {
            return [
                'product_name' => 'required|string|max:255',
                'product_description' => 'nullable|string',
                'product_price' => 'required|numeric|min:0',
                'discount' => 'nullable|numeric|min:0|max:100',
                'product_size' => 'nullable|array',
                'product_color' => 'nullable|array',
                'product_image' => 'nullable|array',
                'product_brand' => 'nullable|string|max:255',
                'product_quantity' => 'required|integer|min:0',
                'category_id' => 'uuid|exists:categories,id',
                'category_name' => 'required|string',
                'category_type' => 'required|string',
                'owner_id' => 'uuid|exists:merchants,id', // Ensure the owner exists
            ];
        }

        // Validation rules for updating a product
        if ($routeName === 'merchant.updateproduct') {
            return [
                'product_name' => 'sometimes|string|max:255',
                'product_description' => 'sometimes|string',
                'product_price' => 'sometimes|numeric|min:0',
                'discount' => 'sometimes|numeric|min:0|max:100',
                'product_size' => 'sometimes|array',
                'product_color' => 'sometimes|array',
                'product_image' => 'sometimes|array',
                'product_brand' => 'sometimes|string|max:255',
                'product_quantity' => 'sometimes|integer|min:0',
                'category_id' => 'uuid|exists:category,id',
                'category_name' => 'sometimes|string',
                'category_type' => 'sometimes|string|unique:categories,category_type,NULL,id,owner_id,' . $ownerId,
                'owner_id' => 'uuid|exists:merchants,id', // Ensure the owner exists
            ];
        }

        // Default to empty rules for unsupported routes
        return [];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        // Customize the response when validation fails
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
