<?php

namespace App\Domains\Wallet\Withdrawal\Actions;

use App\Domains\Utils\Enums\ActivityTypesEnum;
use App\Domains\Utils\Enums\SecurityTypesEnum;
use App\Domains\Utils\Traits\ActivityTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SetupTransactionPin
{
    use ActivityTrait;

    public ?User $user = null;

    /**
     * @throws ValidationException
     */
    public function execute(Request $request): Model
    {
        Validator::make($request->all(), [
            'pin' => "required|digits:6",
        ])->validate();

        $this->user = auth()->user();

        $this->setActivity(ActivityTypesEnum::TRANSACTION_PIN_SETUP->value, $this->user);

        return $this->user->securities()->updateOrCreate(
            ['name' => SecurityTypesEnum::TRANSACTION_PIN->value],
            ['value' => Crypt::encryptString($request->pin)],
        );
    }
}
