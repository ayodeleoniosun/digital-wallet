<?php

namespace App\Domains\Wallet\Withdrawal\Http\Requests;

use App\Domains\Utils\Traits\OverrideDefaultValidationMethodsTrait;
use Illuminate\Foundation\Http\FormRequest;

class WithdrawRequest extends FormRequest
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
            'bank_name' => ['required'],
            'account_name' => ['required'],
            'account_number' => ['required', 'digits:11'],
        ];
    }
}
