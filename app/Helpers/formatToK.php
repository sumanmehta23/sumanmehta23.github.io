<?php
// Format number to K, M, B, etc. (e.g., 1000 => 1K, 1500000 => 1.5M)
if (!function_exists('formatToK')) {
    function formatToK($num, $precision = 1)
    {
        if ($num >= 1000000000) {
            return round($num / 1000000000, $precision) . 'B';
        }
        if ($num >= 1000000) {
            return round($num / 1000000, $precision) . 'M';
        }
        if ($num >= 1000) {
            return round($num / 1000, $precision) . 'K';
        }
        return $num;
    }
}
