<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class DetailPenjualan extends Model
{
    protected $connection = 'mongodb';

    protected $keyType = 'string';

    protected $fillable = [
        'penjualan_id',
        'hp_id',
        'modal_terakhir',
        'harga_jual_unit',
        'laba_rugi'
    ];

    protected function casts(): array
    {
        return [
            'modal_terakhir' => 'float',
            'harga_jual_unit' => 'float',
            'laba_rugi' => 'float',
            'penjualan_id' => 'string',
            'hp_id' => 'string',
        ];
    }

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function hp(): BelongsTo
    {
        return $this->belongsTo(Hp::class, 'hp_id');
    }
}
