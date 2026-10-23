<?php

namespace App\Support;

class Money
{
    public static function add(string|float|int $a, string|float|int $b): string
    {
        return bcadd(self::clean($a), self::clean($b), 2);
    }

    public static function sub(string|float|int $a, string|float|int $b): string
    {
        return bcsub(self::clean($a), self::clean($b), 2);
    }

    public static function mul(string|float|int $a, string|float|int $b): string
    {
        return bcmul(self::clean($a), self::clean($b), 2);
    }

    public static function div(string|float|int $a, string|float|int $b): string
    {
        $bClean = self::clean($b);
        if (bccomp($bClean, '0.00', 4) === 0) {
            return '0.00';
        }

        return bcdiv(self::clean($a), $bClean, 2);
    }

    public static function percent(string|float|int $amount, string|float|int $percent): string
    {
        $raw = bcdiv(bcmul(self::clean($amount), self::clean($percent), 6), '100', 6);

        return self::roundHalfUp($raw);
    }

    public static function roundHalfUp(string $value): string
    {
        $sign = bccomp($value, '0', 6) >= 0 ? '0.005' : '-0.005';

        return bcadd($value, $sign, 2);
    }

    public static function gt(string|float|int $a, string|float|int $b): bool
    {
        return bccomp(self::clean($a), self::clean($b), 2) === 1;
    }

    public static function gte(string|float|int $a, string|float|int $b): bool
    {
        return bccomp(self::clean($a), self::clean($b), 2) >= 0;
    }

    public static function lt(string|float|int $a, string|float|int $b): bool
    {
        return bccomp(self::clean($a), self::clean($b), 2) === -1;
    }

    public static function lte(string|float|int $a, string|float|int $b): bool
    {
        return bccomp(self::clean($a), self::clean($b), 2) <= 0;
    }

    public static function eq(string|float|int $a, string|float|int $b): bool
    {
        return bccomp(self::clean($a), self::clean($b), 2) === 0;
    }

    public static function format(string|float|int $amount, string $currency = '₹'): string
    {
        return $currency.' '.number_format((float) self::clean($amount), 2);
    }

    private static function clean(string|float|int $val): string
    {
        return (string) number_format((float) $val, 2, '.', '');
    }
}
