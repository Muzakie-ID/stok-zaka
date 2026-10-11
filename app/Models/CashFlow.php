<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use MongoDB\Laravel\Eloquent\Model;

class CashFlow extends Model
{
    protected $connection = 'mongodb';

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'amount' => 'float',
        'reference_id' => 'string',
    ];

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
