<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'provider',
        'is_active',
        'fee_percentage',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'fee_percentage' => 'decimal:2',
    ];

    public function paymentDetails()
    {
        return $this->hasMany(PaymentDetail::class, 'method_id');
    }
}
