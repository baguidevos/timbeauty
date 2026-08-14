<?php

namespace App\Helpers;

class FormatHelper
{
    /**
     * Format an amount as FCFA with space-separated thousands.
     * e.g., 12500 → "12 500 FCFA"
     */
    public static function formatFCFA($amount): string
    {
        if ($amount === null) {
            return '0 FCFA';
        }

        $formatted = number_format((float) $amount, 0, ',', ' ');

        return $formatted.' FCFA';
    }

    /**
     * Format a date in French format.
     * e.g., "10/08/2026"
     */
    public static function formatDate($date): string
    {
        if ($date === null) {
            return '';
        }

        if ($date instanceof \DateTimeInterface) {
            return $date->format('d/m/Y');
        }

        return date('d/m/Y', strtotime($date));
    }

    /**
     * Format a time in French format.
     * e.g., "14h30"
     */
    public static function formatTime($time): string
    {
        if ($time === null) {
            return '';
        }

        if ($time instanceof \DateTimeInterface) {
            return $time->format('H\hi');
        }

        $parsed = strtotime($time);
        if ($parsed === false) {
            return $time;
        }

        return date('H\hi', $parsed);
    }
}
