<?php
class Jalali
{
    public static function toGregorian($jy, $jm, $jd)
    {
        $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
 
        $jy = $jy - 979;
        $jm = $jm - 1;
        $jd = $jd - 1;
 
        $j_day_no = 365 * $jy + (int)($jy / 33) * 8 + (int)((($jy % 33) + 3) / 4);
        for ($i = 0; $i < $jm; ++$i) {
            $j_day_no += $j_days_in_month[$i];
        }
        $j_day_no += $jd;
 
        $g_day_no = $j_day_no + 79;
 
        $gy = 1600;
        $gm = 0;
        $gd = 0;
 
        while ($g_day_no >= 366) {
            $is_leap = ($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0);
            $days_in_year = $is_leap ? 366 : 365;
            if ($g_day_no >= $days_in_year) {
                $g_day_no -= $days_in_year;
                ++$gy;
            } else {
                break;
            }
        }
 
        $is_leap = ($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0);
        $g_days_in_month[1] = $is_leap ? 29 : 28;
 
        for ($gm = 0; $gm < 12; ++$gm) {
            if ($g_day_no < $g_days_in_month[$gm]) {
                $gd = $g_day_no + 1;
                break;
            }
            $g_day_no -= $g_days_in_month[$gm];
        }
 
        return array($gy, $gm + 1, $gd);
    }
}