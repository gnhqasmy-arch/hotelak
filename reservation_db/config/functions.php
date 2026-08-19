<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/reservation_db');
}
// config/functions.php

function getOld($key, $default = '') {
    return isset($_GET[$key]) ? htmlspecialchars($_GET[$key]) : $default;
}

function getCoverImage($pdo, $accommodation_id) {
    $stmt = $pdo->prepare("SELECT image_url FROM accommodation_gallery WHERE accommodation_id = ? AND is_cover = 1 LIMIT 1");
    $stmt->execute([$accommodation_id]);
    $img = $stmt->fetch();
    return $img ? $img['image_url'] : 'assets/images/default.jpg';
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function escape($value) {
    global $pdo;
    return htmlspecialchars(strip_tags(trim($value)));
}

// ====== محاسبه تعداد شب‌های اقامت (با تبدیل شمسی به میلادی) ======
function nightsBetween($date1, $date2) {
    // اگر تاریخ‌ها خالی بودند، ۰ برگردان
    if (empty($date1) || empty($date2)) {
        return 0;
    }
    
    // تبدیل تاریخ‌های شمسی به میلادی
    $date1_greg = shamsiToGregorian($date1);
    $date2_greg = shamsiToGregorian($date2);
    
    // اگر تبدیل ناموفق بود، ۰ برگردان
    if (empty($date1_greg) || empty($date2_greg)) {
        return 0;
    }
    
    // تبدیل به تایم‌استمپ و محاسبه اختلاف
    $d1 = strtotime($date1_greg);
    $d2 = strtotime($date2_greg);
    
    if (!$d1 || !$d2 || $d1 >= $d2) {
        return 0;
    }
    
    $diff = $d2 - $d1;
    return floor($diff / (60 * 60 * 24));
}

// دریافت نام شهر از ID
function getCityName($city_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT name FROM cities WHERE id = ?");
    $stmt->execute([$city_id]);
    $row = $stmt->fetch();
    return $row ? $row['name'] : '';
}

// دریافت امکانات یک اقامتگاه (رشته ای)
function getAmenitiesString($accommodation_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT am.name FROM accommodation_amenities aa
        JOIN amenities am ON aa.amenity_id = am.id
        WHERE aa.accommodation_id = ?
    ");
    $stmt->execute([$accommodation_id]);
    $amenities = $stmt->fetchAll();
    $names = array_column($amenities, 'name');
    return implode(' - ', $names);
}


// ====== بارگذاری کتابخانه Jalali ======


require_once __DIR__ . '/../includes/Jalali.php';

function persianToGregorian($persianDate) {
    if (empty($persianDate)) {
        return '';
    }
    
    $persianDate = trim($persianDate);
    
    if (!preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $persianDate, $matches)) {
        return '';
    }
    
    $year = (int)$matches[1];
    $month = (int)$matches[2];
    $day = (int)$matches[3];
    
    // تبدیل با کتابخانه Jalali
    $gregorian = Jalali::toGregorian($year, $month, $day);
    
    return sprintf('%04d-%02d-%02d', $gregorian[0], $gregorian[1], $gregorian[2]);
}

// ====== تبدیل تاریخ میلادی به شمسی (مستقل از کتابخانه) ======
function gregorianToJalali($gy, $gm, $gd) {
    $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);

    $gy = $gy - 1600;
    $gm = $gm - 1;
    $gd = $gd - 1;

    $g_day_no = 365 * $gy + (int)(($gy + 3) / 4) - (int)(($gy + 99) / 100) + (int)(($gy + 399) / 400);
    for ($i = 0; $i < $gm; ++$i) {
        $g_day_no += $g_days_in_month[$i];
    }
    $g_day_no += $gd;

    $j_day_no = $g_day_no - 79;
    $j_np = (int)($j_day_no / 12053);
    $j_day_no %= 12053;
    $jy = 979 + 33 * $j_np + 4 * (int)($j_day_no / 1461);
    $j_day_no %= 1461;

    if ($j_day_no >= 366) {
        $jy += (int)(($j_day_no - 1) / 365);
        $j_day_no = ($j_day_no - 1) % 365;
    }

    for ($i = 0; $i < 11 && $j_day_no >= $j_days_in_month[$i]; ++$i) {
        $j_day_no -= $j_days_in_month[$i];
    }
    $jm = $i + 1;
    $jd = $j_day_no + 1;

    return array($jy, $jm, $jd);
}

// ====== تبدیل تاریخ شمسی به میلادی (دستی و تضمینی) ======
function shamsiToGregorian($shamsiDate) {
    if (empty($shamsiDate)) {
        return '';
    }
    
    // پاکسازی ورودی
    $shamsiDate = trim($shamsiDate);
    
    // تبدیل اعداد فارسی به انگلیسی
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $shamsiDate = str_replace($persian, $english, $shamsiDate);
    
    // بررسی فرمت YYYY/MM/DD
    if (!preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $shamsiDate, $matches)) {
        return '';
    }
    
    list(, $year, $month, $day) = $matches;
    $year = (int)$year;
    $month = (int)$month;
    $day = (int)$day;
    
    // الگوریتم تبدیل شمسی به میلادی (با استفاده از mktime)
    $g_y = $year + 621;
    $g_m = $month;
    $g_d = $day;
    
    // تصحیح برای روزهای آخر سال
    if ($month > 10 || ($month == 10 && $day > 22)) {
        $g_y++;
        $g_m = $month - 9;
        $g_d = $day - 22;
        if ($g_m == 0) {
            $g_m = 12;
            $g_y--;
        }
    }
    
    $timestamp = mktime(0, 0, 0, $g_m, $g_d, $g_y);
    if ($timestamp === false) {
        return '';
    }
    
    return date('Y-m-d', $timestamp);
}

?>
