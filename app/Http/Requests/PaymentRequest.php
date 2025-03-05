<?php
namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PaymentRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        // Set default values for nullable fields
        $this->merge([

             'amount'=> $this->input('amount'),
            'reason' => $this->input('reason'),
            'merchantId' => $this->input('merchantId'),
            'signedToken' => $this->input('signedToken'),
            'successRedirectUrl' => $this->input('successRedirectUrl'),
            'failureRedirectUrl'=> $this->input('failureRedirectUrl'),
            'notifyUrl'=> $this->input('notifyUrl'),
            'cancelRedirectUrl'=> $this->input('cancelRedirectUrl'),
        ]);
    }
    public function rules()
    {
        return [
            'id'=>'required|string|max:255',
            'amount'=> 'required|string|max:255',
            'reason'=> 'required|string|max:255',
            'merchantId'=> 'required|string|max:255',
            'signedToken'=> 'required|string|max:255',
            'successRedirectUrl'=> 'required|string|max:255',
            'failureRedirectUrl'=> 'required|string|max:255',
            'notifyUrl'=> 'required|string|max:255',
            'cancelRedirectUrl'=> 'required|string|max:255',
];

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
