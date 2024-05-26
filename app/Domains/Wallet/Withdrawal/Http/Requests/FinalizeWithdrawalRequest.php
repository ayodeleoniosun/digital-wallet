<?php

namespace App\Domains\Wallet\Withdrawal\Http\Requests;

use App\Domains\Utils\Traits\OverrideDefaultValidationMethodsTrait;
use Illuminate\Foundation\Http\FormRequest;

class FinalizeWithdrawalRequest extends FormRequest
{
    use OverrideDefaultValidationMethodsTrait;

    public object $withdrawalSettings;

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
            'otp' => ['required', 'digits:6'],
            'pin' => ['required', 'digits:6'],
        ];
    }
}
