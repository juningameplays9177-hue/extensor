<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receivable extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_id',
        'description',
        'value',
        'due_date',
        'payment_date',
        'receipt_number',
        'invoice_number',
        'status',
        'notes',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isOverdue(): bool
    {
        return !$this->isPaid() && $this->due_date < now()->startOfDay();
    }
}
