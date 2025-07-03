<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterMerchantRequest extends FormRequest
{

    public function rules()
    {
        $routeName = $this->route()->getName();

        if ($routeName === 'merchant.register') {
            return [
                'firstname' => 'required|string',
                'lastname' => 'required|string',
                'phone' => 'required|string|max:13|unique:merchants,phone',
                'email' => 'nullable|string|email',
                'password' => 'required|string|min:6',
                'shopname' => 'required|string|max:255',
                'logo'=> 'nullable|string|max:255',
                'license' => 'required|string|max:255',
                'tinnumber' => 'required|string|max:255',
                'role' => 'nullable|string',
            ];
        }

        if ($routeName === 'merchant.login') {
            return [
                'phone' => 'required|string|max:13',
                'password' => 'required|string|min:6',
                'role' => 'required|string',
            ];
        }

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
