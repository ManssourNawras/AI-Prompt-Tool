<?php


namespace App\Traits;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

trait JsonErrors
{
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()
                ->json(
                    [
                        'status'=>false,
                        'data '=>null,
                        'message'=>$validator->errors()->first()
                    ]
                    , 422)
        );
    }
}
