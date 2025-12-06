<?php

namespace App\Models;

class StageCategory
{
    const VENEER   = 'veneer';
    const PRE_FAB  = 'pre_fab';
    const PLYWOOD  = 'plywood';
    const WAREHOUSE  = 'Warehouse';

    public static function list(): array
    {
        return [
            self::VENEER  => 'Veneer',
            self::PRE_FAB => 'Pre-Fab',
            self::PLYWOOD => 'Plywood',
            self::WAREHOUSE => 'Warehouse',
        ];
    }

    public static function label($value): string
    {
        return self::list()[$value] ?? 'Unknown';
    }
}
