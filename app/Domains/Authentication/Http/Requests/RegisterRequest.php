<?php

namespace App\Domains\Authentication\Http\Requests;

use App\Domains\Utils\Traits\OverrideDefaultValidationMethodsTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    use OverrideDefaultValidationMethodsTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'firstname' => ['required'],
            'lastname' => ['required'],
            'phone' => [
                Rule::unique('profiles')->whereNull('deleted_at'),
                'regex:/^\+234(81|80|70|90|91)[0-9]{8}$/',
            ],
            'email' => 'required|email|unique:users',
            'password' => [
                'required', Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ];
    }
}
