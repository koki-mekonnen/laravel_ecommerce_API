<?php
namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequest extends FormRequest
{

    public function rules()
    {
        $routeName = $this->route()->getName();

        if ($routeName === 'user.register') {
            return [
                'firstname' => 'nullable|string',
                'lastname'  => 'nullable|string',
                'email'     => 'nullable|string|email',
                'phone'     => 'required|string|max:13|unique:users,phone',
                'password'  => 'required|string|min:6',
                'role'      => 'nullable|string',
            ];
        }

        if ($routeName === 'user.login') {
            return [
                'phone'    => 'required|string|max:13',
                'password' => 'required|string|min:6',
                'role'     => 'required|string',
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
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
