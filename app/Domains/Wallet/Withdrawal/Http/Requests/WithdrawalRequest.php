<?php

namespace App\Domains\Wallet\Withdrawal\Http\Requests;

use App\Domains\Utils\Traits\OverrideDefaultValidationMethodsTrait;
use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
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
        $this->withdrawalSettings = json_decode(Setting::where('name', 'withdraw')->value('value'));

        return [
            'currency' => ['required', 'string'],
            'payment_option_id' => ['required', 'string'],
            'reason' => ['sometimes', 'string', 'max:50'],
            'amount' => [
                'required',
                'numeric',
                'min:'.$this->withdrawalSettings->minimum,
                'max:'.$this->withdrawalSettings->maximum,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => "The minimum amount to be withdrawn is ".number_format($this->withdrawalSettings->minimum)." naira",
            'amount.max' => "The maximum amount to be withdrawn is ".number_format($this->withdrawalSettings->maximum)." naira",
        ];
    }
}
