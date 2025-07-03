<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{

    public function rules()
    {
        $routeName = $this->route()->getName();

        if ($routeName === 'merchant.createcategory') {
            return [
                'category_name' => 'required|string',
                'category_type' => [
    'required',
    'string',
    Rule::unique('categories')->where(function ($query) {
        return $query->where('owner_id', request()->input('owner_id'));
    }),
],
                'owner_id' => 'string',

            ];
        }

        if ($routeName === 'merchant.updatecategory') {
            return [
                'category_name' => 'string',
               'category_type' => [
    'string',
    Rule::unique('categories')->ignore($this->route('id'))->where(function ($query) {
        return $query->where('owner_id', request()->input('owner_id'));
    }),
],
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
