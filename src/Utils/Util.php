<?php

namespace App\Utils;

use DateTime;

class Util
{
    public static function isExpired(DateTime $date): bool
    {
        $now = new DateTime();
        if($date < $now) {
            return true;
        }

        return false;
    }
}
