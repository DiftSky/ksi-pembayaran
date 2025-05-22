<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Crypt;
use Exception;

class EncryptionHelper
{
    /**
     * Encrypt the given value.
     *
     * @param  mixed  $value
     * @return string|null
     */
    public static function encrypt($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::encryptString((string) $value);
        } catch (Exception $e) {
            report($e);
            return null;
        }
    }

    /**
     * Decrypt the given value.
     *
     * @param  string|null  $encrypted
     * @return string|null
     */
    public static function decrypt($encrypted)
    {
        if (empty($encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (Exception $e) {
            report($e);
            return null;
        }
    }

    /**
     * Mask a credit card number or any sensitive string.
     *
     * @param  string  $number
     * @param  int  $visibleDigits
     * @return string
     */
    public static function maskNumber($number, $visibleDigits = 4)
    {
        if (empty($number)) {
            return '';
        }

        $length = strlen($number);
        
        if ($length <= $visibleDigits) {
            return $number;
        }

        $maskedLength = $length - $visibleDigits;
        $maskedPart = str_repeat('*', $maskedLength);
        $visiblePart = substr($number, $maskedLength);

        return $maskedPart . $visiblePart;
    }

    /**
     * Hash a value using a one-way algorithm (for passwords, etc.)
     *
     * @param  string  $value
     * @return string|null
     */
    public static function hash($value)
    {
        if (empty($value)) {
            return null;
        }

        return password_hash($value, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify a value against a hash.
     *
     * @param  string  $value
     * @param  string  $hashedValue
     * @return bool
     */
    public static function verifyHash($value, $hashedValue)
    {
        if (empty($value) || empty($hashedValue)) {
            return false;
        }

        return password_verify($value, $hashedValue);
    }
}
