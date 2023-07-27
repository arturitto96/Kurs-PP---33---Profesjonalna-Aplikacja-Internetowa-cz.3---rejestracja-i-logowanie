<?php

namespace App;

/**
 * Supplementary methods class
 * 
 * PHP version 7.0
 */
class SupplementaryMethods {
    /**
     * Get the today date
     *
     * @return void
     */
    public static function getTodayDate() {
        return date("Y-m-d");
    }

    /**
     * Get the date of first day of this month
     *
     * @return void
     */
    public static function getFirstDateOfThisMonth() {
        return date('Y') . '-' . date('m') . '-01';
    }
}