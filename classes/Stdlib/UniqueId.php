<?php

namespace Stdlib;

class UniqueId
{
    private function __construct()
    {
    }

    public static function generate($length)
    {
        $uniqueId = '';
        for ($i = 0; $i < ceil($length / 13); $i++) {
            $uniqueId .= uniqid();
        }
        return substr($uniqueId, 0, $length);
    }
}