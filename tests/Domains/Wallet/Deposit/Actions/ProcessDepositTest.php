<?php

namespace Tests\Domains\Wallet\Deposit\Actions;

use App\Domains\Utils\Enums\ActivityTypesEnum;
use App\Domains\Utils\Enums\DepositTypesEnum;
use App\Domains\Utils\Enums\TransactionStatusEnum;
use App\Domains\Utils\Enums\TransactionTypesEnum;
use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Wallet\Deposit\Actions\ProcessDeposit;
use App\Domains\Wallet\Deposit\Jobs\CompleteDepositJob;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Mockery;
use Tests\Domains\Wallet\Examples;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->user->profile()->create([
        'user_id' => $this->user->id,
        'unique_id' => uniquePrefix(),
        'phone' => '+2348123456789',
    ]);

    $this->user->account()->create();

    $this->actingAs($this->user);

    $this->request = new Request();

    $this->invalidReference = 'invalidReference';

    Http::fake([
        'https://api.paystack.co/transaction/verify/'.$this->invalidReference => Http::response(Examples::invalidTransactionReference()),
    ]);

    $this->mock = Mockery::mock('overload:'.Redis::class)->makePartial();
});

it('should throw an error if the user does not exist', function () {
    $this->request->merge(Examples::depositWebhookEvent());

    (new ProcessDeposit())->execute($this->request);
})->throws(CustomException::class, 'User does not exist');

it('should throw an error if the status is not success', function () {
    $this->request->merge(Examples::depositWebhookEvent('error', $this->user->email));

    (new ProcessDeposit())->execute($this->request);

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $this->user->id,
        'type' => ActivityTypesEnum::FAILED_DEPOSIT->value,
    ]);

})->throws(CustomException::class, 'Deposit not successful from payment provider');

it('should throw an error if the reference is invalid', function () {
    $this->request->merge(Examples::depositWebhookEvent('success', $this->user->email, $this->invalidReference));

    (new ProcessDeposit())->execute($this->request);

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $this->user->id,
        'type' => ActivityTypesEnum::INVALID_DEPOSIT_REFERENCE->value,
    ]);

})->throws(CustomException::class, 'Transaction reference not found');

it('should throw an error in an attempt to credit the user more than once', function () {
    Queue::fake();

    $this->request->merge(Examples::depositWebhookEvent('success', $this->user->email));
    $webhookEvent = Examples::depositWebhookEvent();

    $reference = $webhookEvent['data']['reference'];
    $uniqueId = $this->user->id."-".$reference;
    $amount = $webhookEvent['data']['amount'];

    $this->mock->shouldReceive('set')
        ->with($uniqueId, true, 'EX', 300)
        ->andReturn(true);

    $this->mock->shouldReceive('get')
        ->with($uniqueId)
        ->andReturn(true);

    (new ProcessDeposit())->execute($this->request);
    (new CompleteDepositJob($uniqueId, $amount, $reference, $this->user))->handle();
})->throws(CustomException::class, 'Deposit already completed');

it('should credit user account if he has not been previously credited', function () {
    Queue::fake();

    $this->request->merge(Examples::depositWebhookEvent('success', $this->user->email));
    $webhookEvent = Examples::depositWebhookEvent();

    $reference = $webhookEvent['data']['reference'];
    $uniqueId = $this->user->id."-".$reference;
    $amount = $webhookEvent['data']['amount'];

    $this->mock->shouldReceive('set')
        ->with($uniqueId, true, 'EX', 300)
        ->andReturn(true);

    $this->mock->shouldReceive('get')
        ->with($uniqueId)
        ->andReturn(false);

    (new ProcessDeposit())->execute($this->request);

    (new CompleteDepositJob($uniqueId, $amount, $reference, $this->user))->handle();

    $this->assertDatabaseHas('deposits', [
        'user_id' => $this->user->id,
        'amount' => $amount,
        'reference' => $reference,
        'type' => DepositTypesEnum::EXTERNAL->value,
    ]);

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $this->user->id,
        'type' => ActivityTypesEnum::DEPOSIT_COMPLETED->value,
    ]);

    $this->assertDatabaseHas('accountings', [
        'user_id' => $this->user->id,
        'amount' => $amount,
        'type' => TransactionTypesEnum::DEPOSIT->value,
        'status' => TransactionStatusEnum::SUCCESSFUL->value,
        'accountable_type' => Deposit::class,
    ]);
});
