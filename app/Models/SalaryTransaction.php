<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryTransaction extends Model
{
    public const TYPE_EARN = 'earn';

    public const TYPE_PAID = 'paid';

    protected $fillable = [
        'user_id',
        'admin_id',
        'type',
        'amount',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /** Saldo saat ini untuk seorang user (earn minus paid). */
    public static function saldoFor(int $userId): float
    {
        return (float) static::where('user_id', $userId)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'earn' THEN amount ELSE -amount END), 0) as saldo")
            ->value('saldo');
    }
}
