<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailySaldoGastoItem extends Model
{
    protected $table = 'daily_saldo_gastos_items';

    protected $fillable = [
        'ref_date',
        'sort_order',
        'name',
        'value',
    ];

    protected $casts = [
        'ref_date' => 'date',
        'value' => 'decimal:2',
    ];
}
