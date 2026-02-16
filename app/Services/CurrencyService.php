<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Setting;
use App\Models\UserWallet;
use App\Models\WithdrawMethod;
use App\Models\WithdrawRequest;
use App\Models\WithdrawAccount;
use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Models\TransactionType;
use App\Models\TransactionStatus;
use App\Models\TransactionCategory;
use App\Models\TransactionSubCategory;
use App\Models\TransactionFee;

class CurrencyService
{
    public static function convert($amount, $from, $to)
    {
        if ($from == $to) {
            return $amount;
        }

        $rate = static::getRate($to);
        $fromRate = static::getRate($from);

        return number_format(((float)$amount / $fromRate) * $rate, 2);
    }

    public static function getRate($code): float
    {
        $currency = $code instanceof Currency ? $code :  Currency::where('code', $code)->first();

        return $currency?->rate ?? 1;
    }
}
