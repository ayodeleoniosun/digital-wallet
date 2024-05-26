<?php

namespace App\Models;

use App\Domains\Utils\Enums\WithdrawalStatusEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Withdrawal extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getWithdrawalStatus(): Attribute
    {
        return new Attribute(
            get: fn() => WithdrawalStatusEnum::from($this->status)
        );
    }
}
