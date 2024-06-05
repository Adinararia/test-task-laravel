<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserCreateRequest extends FormRequest
{
    private int $statusCode = 422;

    public function rules(): array
    {
        return [
            'name'        => 'required|string|min:2|max:60',
            'email'       => 'required|email|regex:/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/',
            'phone'       => 'required|string|regex:/^\+380\d{9}$/',
            'position_id' => 'required|exists:positions,id|exists:positions,id',
            'photo'       => 'required|image|mimes:jpeg,jpg,png|max:5120|dimensions:min_width=70,min_height=70',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => false,
                'message' => "Validation failed",
                'fails'   => $validator->errors(),
            ], $this->statusCode)
        );
    }

    public function after(): array
    {
        return [
            function (\Illuminate\Validation\Validator $validator) {
                if ($validator->errors()->isEmpty()) {
                    $email = $this->request->get('email');
                    $phone = $this->request->get('phone');
                    $validator->errors()->addIf(
                        !$validator->validateUnique('email', $email, ['users']),
                        'email',
                        'User with this email already exist',
                    );
                    $validator->errors()->addIf(
                        !$validator->validateUnique('phone', $phone, ['users']),
                        'phone',
                        'User with this phone already exist'
                    );
                    if ($validator->errors()->isNotEmpty()) {
                        $this->statusCode = 409;
                    }
                }
            },
        ];
    }
}
