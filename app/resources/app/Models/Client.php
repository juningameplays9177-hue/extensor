<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'document',
        'address',
    ];

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }
    
    protected function formattedPhone(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->phone) {
                    return null;
                }
                
                $phone = preg_replace('/\D/', '', $this->phone);
                
                if (strlen($phone) === 11) {
                    return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7);
                }
                
                if (strlen($phone) === 10) {
                    return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 4) . '-' . substr($phone, 6);
                }
                
                return $this->phone;
            },
        );
    }
}
