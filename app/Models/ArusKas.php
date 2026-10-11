<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ArusKas extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'arus_kas';

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'float',
    ];
}
