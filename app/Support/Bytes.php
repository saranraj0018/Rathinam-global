
<?php

namespace App\Support;

class Bytes
{
public static function human(int $bytes, int $precision = 1): string
{
if ($bytes <= 0) {
    return '0 B' ;
    }

    $units=['B', 'KB' , 'MB' , 'GB' , 'TB' ];
    $power=min((int) floor(log($bytes, 1024)), count($units) - 1);

    return round($bytes / (1024 ** $power), $precision) . ' ' . $units[$power];
    }
    }
