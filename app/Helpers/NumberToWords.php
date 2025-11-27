<?php
namespace App\Helpers;

class NumberToWords
{
    public static function convert($number)
    {
        // Example: use PHP's intl extension
        $formatter = new \NumberFormatter('en', \NumberFormatter::SPELLOUT);
        return ucfirst($formatter->format($number));
    }
}
