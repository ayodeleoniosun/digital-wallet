<?php

namespace App\Domains\Authentication\Actions;

use App\Domains\Utils\Enums\ActivityTypesEnum;
use App\Domains\Utils\Traits\ActivityTrait;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreateUser
{
    use ActivityTrait;

    private ?User $user = null;

    public function execute(Request $request): User
    {
        DB::transaction(function () use ($request) {
            $this->user = User::create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => strtolower($request->email),
                'password' => bcrypt(hash('sha256', $request->password)),
                'email_verified_at' => now(),
            ]);

            Profile::create([
                'user_id' => $this->user->id,
                'phone' => $request->phone,
                'unique_id' => uniquePrefix(),
            ]);
        });

        $this->setActivity(ActivityTypesEnum::REGISTER->value, $this->user);

        return $this->user->with('profile')->first();
    }
}
