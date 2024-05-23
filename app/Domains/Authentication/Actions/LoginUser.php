<?php

namespace App\Domains\Authentication\Actions;

use App\Domains\Utils\Enums\ActivityTypesEnum;
use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Utils\Traits\ActivityTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LoginUser
{
    use ActivityTrait;

    private ?User $user = null;

    /**
     * @throws CustomException
     */
    public function execute(Request $request): array
    {
        tap(User::with('profile')
            ->where('email', $request->email)
            ->first(), function (?User $user) {
            if ($user) {
                $this->user = $user;
            }
        });

        if (!isset($this->user)) {
            throw new CustomException('User does not exist.', Response::HTTP_NOT_FOUND);
        }

        $password = hash('sha256', $request->password);

        $this->validatePassword($password);

        $this->validateEmailVerified();

        $this->createAccountIfNotExist();

        $this->user->tokens()->delete();

        $token = $this->user->createToken(Str::slug($this->user->firstname))->plainTextToken;

        $this->setActivity(ActivityTypesEnum::LOGIN->value, $this->user);

        return [
            'id' => $this->user->id,
            'token' => $token,
            'firstname' => $this->user->firstname,
            'lastname' => $this->user->lastname,
            'email' => $this->user->email,
            'phone_number' => $this->user->profile->phone,
        ];
    }

    /**
     * @throws CustomException
     */
    private function validatePassword(string $password): void
    {
        if (!Hash::check($password, $this->user->password)) {
            throw new CustomException('Incorrect login credentials.');
        }
    }

    /**
     * @throws CustomException
     */
    private function validateEmailVerified(): void
    {
        if (!$this->user->email_verified_at) {
            throw new CustomException('Email not yet verified.', Response::HTTP_FORBIDDEN);
        }
    }

    public function createAccountIfNotExist(): void
    {
        $account = $this->user->account()->first();

        if (!$account) {
            $this->user->account()->create();
        }
    }
}
