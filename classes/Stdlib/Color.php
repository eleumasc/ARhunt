<?php

namespace Stdlib;

class Color
{
    private function __construct()
    {
    }

    public static function getContrastColor($hex) {
        /**
         * From this W3C document: http://www.w3.org/TR/AERT#color-contrast
         * Color brightness is determined by the following formula:
         * ((R * 299) + (G * 587) + (B * 114)) / 1000
         */
        $threshold = 130; /* about half of 256. Lower threshold equals more dark text on dark background */
        $hexToR = function($h) {
            return hexdec(substr($h, 0, 2));
        };
        $hexToG = function($h) {
            return hexdec(substr($h, 2, 2));
        };
        $hexToB = function($h) {
            return hexdec(substr($h, 4, 2));
        };
        $hRed = $hexToR($hex);
        $hGreen = $hexToG($hex);
        $hBlue = $hexToB($hex);
        $cBrightness = (($hRed * 299) + ($hGreen * 587) + ($hBlue * 114)) / 1000;
        return ($cBrightness > $threshold ? '000000' : 'ffffff');
    }
}