<?php

use App\Exceptions\InsufficientBalanceException;
use Carbon\Carbon;

if (!function_exists('format_decimal')) {
    /**
     * Format number to decimal with 2 digits
     * 
     * @param float|int|null $number
     * @return string
     */
    function format_decimal($number)
    {
        return number_format($number, 2, '.', '');
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format number to currency format
     * 
     * @param float|int|null $number
     * @return string
     */
    function format_currency($number)
    {
        return number_format($number, 2, ',', '.');
    }
} 


if (!function_exists('validate_balance')) {
    /**
     * Validate if balance is sufficient
     * 
     * @param float|int $balance
     * @param float|int $amount
     * @return bool
     */
    function validate_balance($balance, $amount)
    {
        if ((float) $balance < (float) $amount) {
            throw new InsufficientBalanceException(
                "Insufficient balance. Available: " . format_decimal($balance) . 
                ", Required: " . format_decimal($amount)
            );
        }

        return true;
    }
}

if (!function_exists('toMilliseconds')) {
    /**
     * Convert datetime to milliseconds since epoch
     *
     * @param string|Carbon $datetime
     * @return int
     */
    function toMilliseconds($datetime)
    {
        if (!$datetime instanceof Carbon) {
            $datetime = Carbon::parse($datetime);
        }
        
        return $datetime->timestamp * 1000;
    }
}

