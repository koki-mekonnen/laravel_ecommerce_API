<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CategoryRequest extends FormRequest
{

    public function rules()
    {
        $routeName = $this->route()->getName();

        if ($routeName === 'merchant.createcategory') {
            return [
                'category_name' => 'required|string',
                'category_type' => 'required|string|unique:categories,category_type,NULL,id,owner_id,' . $this->owner_id,
                'owner_id' => 'string',

            ];
        }

        if ($routeName === 'merchant.updatecategory') {
            return [
                'category_name' => 'string',
                'category_type' => 'string|unique:categories,category_type,NULL,id,owner_id,' . $this->owner_id,
                'owner_id' => 'string',

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
