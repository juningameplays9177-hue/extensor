<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OldClient extends Model
{
    protected $fillable = [
        'name',
        'receipt_number',
        'amount_due',
        'checked',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'checked' => 'boolean',
    ];
}
