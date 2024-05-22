<?php

namespace App\Domains\Authentication\Actions;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreateUser
{
    private ?User $user = null;

    public function execute(Request $request): User
    {
        DB::transaction(function () use ($request) {
            $this->user = User::create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => strtolower($request->email),
                'password' => bcrypt(hash('sha256', $request->password)),
            ]);

            Profile::create([
                'user_id' => $this->user->id,
                'phone' => $request->phone,
                'unique_id' => uniquePrefix(),
            ]);
        });

        event(new Registered($this->user));

        return $this->user->with('profile')->first();
    }
}
