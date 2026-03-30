<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'client_id',
        'container_id',
        'depot_id',
        'street',
        'number',
        'complement',
        'photo',
        'allocated_at',
        'removed_at',
        'status',
        'service_done',
        'service_done_at',
        'value',
    ];

    protected $casts = [
        'allocated_at' => 'datetime',
        'removed_at' => 'datetime',
        'service_done_at' => 'datetime',
        'service_done' => 'boolean',
        'value' => 'decimal:2',
    ];

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withDefault();
    }

    public function receivables(): HasMany
    {
        return $this->hasMany(\App\Models\Receivable::class);
    }

    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: fn () => collect([$this->street, $this->number, $this->complement])
                ->filter()
                ->implode(', '),
        );
    }

    protected function elapsedHours(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->allocated_at) {
                    return '0min';
                }
                
                try {
                    $allocated = CarbonImmutable::parse($this->allocated_at);
                    $now = now();
                    $totalMinutes = $allocated->diffInMinutes($now);
                    
                    if ($totalMinutes < 60) {
                        return $totalMinutes . 'min';
                    }
                    
                    $hours = floor($totalMinutes / 60);
                    $minutes = $totalMinutes % 60;
                    
                    if ($minutes == 0) {
                        return $hours . 'h';
                    }
                    
                    return $hours . 'h ' . $minutes . 'min';
                } catch (\Exception $e) {
                    return '0min';
                }
            },
        );
    }
    
    protected function elapsedHoursDecimal(): Attribute
    {
        return Attribute::make(
            get: fn () => round(CarbonImmutable::parse($this->allocated_at)->diffInHours(now()), 1),
        );
    }

    protected function alertLevel(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->allocated_at) {
                    return 'normal';
                }
                
                try {
                    $hours = CarbonImmutable::parse($this->allocated_at)->diffInHours(now());
                    
                    if ($hours >= 48) {
                        return 'danger';
                    }

                    if ($hours >= 24) {
                        return 'warning';
                    }

                    return 'normal';
                } catch (\Exception $e) {
                    return 'normal';
                }
            },
        );
    }
}
