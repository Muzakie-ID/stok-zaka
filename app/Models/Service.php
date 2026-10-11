<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class Service extends Model
{
    protected $connection = 'mongodb';

    protected $keyType = 'string';

    protected $fillable = [
        'hp_id',
        'deskripsi',
        'biaya',
        'tanggal_service',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_service' => 'datetime',
            'biaya' => 'float',
            'hp_id' => 'string',
        ];
    }

    public function hp(): BelongsTo
    {
        return $this->belongsTo(Hp::class, 'hp_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\MorphOne<\App\Models\CashFlow, \App\Models\Service> */
    public function cashFlow()
    {
        return $this->morphOne(CashFlow::class, 'reference');
    }

    protected static function booted()
    {
        static::deleting(function ($service) {
            $service->cashFlow()->delete();
        });
    }
}
