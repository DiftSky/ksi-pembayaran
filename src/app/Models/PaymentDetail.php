<?php

namespace App\Models;

use App\Helpers\EncryptionHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'method_id',
        'account_number',
        'bank_name',
        'card_type',
        'last_four_digits',
        'expiry_date',
        'holder_name',
        'notes',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    /**
     * Get the account number.
     *
     * @return string|null
     */
    public function getAccountNumberAttribute($value)
    {
        return $value ? EncryptionHelper::decrypt($value) : null;
    }

    /**
     * Set the account number.
     *
     * @param string $value
     * @return void
     */
    public function setAccountNumberAttribute($value)
    {
        $this->attributes['account_number'] = $value ? EncryptionHelper::encrypt($value) : null;
    }

    /**
     * Get the last four digits.
     *
     * @return string|null
     */
    public function getLastFourDigitsAttribute($value)
    {
        return $value ? EncryptionHelper::decrypt($value) : null;
    }

    /**
     * Set the last four digits.
     *
     * @param string $value
     * @return void
     */
    public function setLastFourDigitsAttribute($value)
    {
        $this->attributes['last_four_digits'] = $value ? EncryptionHelper::encrypt($value) : null;
    }

    /**
     * Get the holder name.
     *
     * @return string|null
     */
    public function getHolderNameAttribute($value)
    {
        return $value ? EncryptionHelper::decrypt($value) : null;
    }

    /**
     * Set the holder name.
     *
     * @param string $value
     * @return void
     */
    public function setHolderNameAttribute($value)
    {
        $this->attributes['holder_name'] = $value ? EncryptionHelper::encrypt($value) : null;
    }

    /**
     * Get the masked account number for display purposes.
     *
     * @return string
     */
    public function getMaskedAccountNumberAttribute()
    {
        return $this->account_number ? EncryptionHelper::maskNumber($this->account_number) : '';
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id');
    }
}
