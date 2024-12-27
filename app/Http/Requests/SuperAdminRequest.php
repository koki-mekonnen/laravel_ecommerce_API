<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SuperAdminRequest extends FormRequest
{

    public function rules()
    {
        $routeName = $this->route()->getName();

        if ($routeName === 'admin.register') {
            return [
                'firstname' => 'required|string',
                'lastname' => 'required|string',
                'phone' => 'required|string|max:13|unique:super_admins,phone',
                'email' => 'nullable|string|email',
                'password' => 'required|string|min:6',
                'role' => 'nullable|string',
            ];
        }

        if ($routeName === 'admin.login') {
            return [
                'email' => 'required|string|email',
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
