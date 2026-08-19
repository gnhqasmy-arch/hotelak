<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

unset($_SESSION['discount_applied']);

if (!isLoggedIn()) {
    $_SESSION['login_message'] = 'لطفاً ابتدا وارد حساب کاربری خود شوید.';
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../../index.php';
    redirect($referer);
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    redirect('../../index.php');
}

$accommodation_id = isset($_POST['accommodation_id']) ? (int)$_POST['accommodation_id'] : 0;
$check_in = isset($_POST['check_in']) ? trim($_POST['check_in']) : '';
$check_out = isset($_POST['check_out']) ? trim($_POST['check_out']) : '';
$guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 1;

$stmt = $pdo->prepare("SELECT price_per_night, max_guests FROM accommodations WHERE id = ?");
$stmt->execute([$accommodation_id]);
$acc = $stmt->fetch();

if (!$acc) {
    $_SESSION['booking_error'] = 'اقامتگاه یافت نشد.';
    redirect('../../index.php');
}

if ($guests > $acc['max_guests']) {
    $_SESSION['booking_error'] = 'تعداد مهمان بیشتر از ظرفیت اقامتگاه است.';
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../../index.php';
    redirect($referer);
}
// ====== تابع تبدیل تاریخ شمسی به میلادی ======


$check_in_greg = shamsiToGregorian($check_in);
$check_out_greg = shamsiToGregorian($check_out);
// ====== بررسی تاریخ خروج قبل از ورود ======
if (empty($check_in_greg) || empty($check_out_greg)) {
    $_SESSION['booking_error'] = 'تاریخ وارد شده نامعتبر است.';
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../../index.php';
    redirect($referer);
}

if ($check_out_greg <= $check_in_greg) {
    $_SESSION['booking_error'] = 'تاریخ خروج باید بعد از تاریخ ورود باشد.';
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../../index.php';
    redirect($referer);
}



// ====== دریافت تاریخ‌های رزرو شده برای این اقامتگاه ======
$reserved_dates = [];
$stmt = $pdo->prepare("SELECT check_in, check_out FROM reservations WHERE accommodation_id = ? AND status IN ('pending', 'confirmed')");
$stmt->execute([$id]);
$reservations = $stmt->fetchAll();

foreach ($reservations as $res) {
    $start = strtotime($res['check_in']);
    $end = strtotime($res['check_out']);
    while ($start < $end) {
        $reserved_dates[] = date('Y/m/d', $start);
        $start = strtotime('+1 day', $start);
    }
}
$reserved_dates = array_unique($reserved_dates);
$reserved_dates_json = json_encode($reserved_dates);


// ====== تابع تشخیص تداخل و بازه‌های آزاد (با حذف تکراری‌ها و ادغام بازه‌ها) ======
function getConflictDetails($pdo, $accommodation_id, $check_in, $check_out) {
    // دریافت همه رزروهای تداخلی (با DISTINCT برای حذف تکراری‌ها)
    $stmt = $pdo->prepare("
        SELECT DISTINCT check_in, check_out FROM reservations 
        WHERE accommodation_id = ? 
        AND status IN ('pending', 'confirmed')
        AND (check_in < ? AND check_out > ?)
    ");
    $stmt->execute([$accommodation_id, $check_out, $check_in]);
    $conflicts = $stmt->fetchAll();

    if (empty($conflicts)) {
        return ['has_conflict' => false];
    }

    // جمع‌آوری بازه‌های شلوغ و یکتا کردن
    $busy_ranges = [];
    foreach ($conflicts as $c) {
        $key = $c['check_in'] . '|' . $c['check_out'];
        $busy_ranges[$key] = ['start' => $c['check_in'], 'end' => $c['check_out']];
    }
    $busy_ranges = array_values($busy_ranges); // بازگرداندن به آرایه معمولی

    // ====== مرتب‌سازی بازه‌ها ======
    usort($busy_ranges, function($a, $b) {
        if ($a['start'] == $b['start']) {
            return 0;
        }
        return ($a['start'] < $b['start']) ? -1 : 1;
    });

    // ====== ادغام بازه‌های همپوشانی ======
    $merged = [];
    foreach ($busy_ranges as $range) {
        if (empty($merged)) {
            $merged[] = $range;
        } else {
            $last = &$merged[count($merged) - 1];
            // اگر بازه فعلی با آخرین بازه همپوشانی دارد یا بلافاصله بعد از آن است
            if ($range['start'] <= $last['end']) {
                if ($range['end'] > $last['end']) {
                    $last['end'] = $range['end'];
                }
            } else {
                $merged[] = $range;
            }
        }
    }

    // پیدا کردن بازه‌های آزاد در بازه درخواستی
    $free_ranges = [];
    $current = $check_in;

    foreach ($merged as $busy) {
        if ($current < $busy['start']) {
            $free_ranges[] = ['start' => $current, 'end' => $busy['start']];
        }
        if ($busy['end'] > $current) {
            $current = $busy['end'];
        }
    }
    if ($current < $check_out) {
        $free_ranges[] = ['start' => $current, 'end' => $check_out];
    }

    return [
        'has_conflict' => true,
        'busy_ranges' => $merged,
        'free_ranges' => $free_ranges
    ];
}

// ====== استفاده در بخش اعتبارسنجی ======
$conflictDetails = getConflictDetails($pdo, $accommodation_id, $check_in, $check_out);

if ($conflictDetails['has_conflict']) {
    $message = "⛔ تاریخ‌های انتخابی شما ($check_in تا $check_out) با رزروهای قبلی تداخل دارد.\n";

    if (!empty($conflictDetails['busy_ranges'])) {
        $busy_str = [];
        foreach ($conflictDetails['busy_ranges'] as $busy) {
            $busy_str[] = $busy['start'] . ' تا ' . $busy['end'];
        }
        $message .= "❌ بازه‌های پر: " . implode('، ', $busy_str) . "\n";
    }

    if (!empty($conflictDetails['free_ranges'])) {
        $free_str = [];
        foreach ($conflictDetails['free_ranges'] as $free) {
            $free_str[] = $free['start'] . ' تا ' . $free['end'];
        }
        $message .= "✅ بازه‌های آزاد: " . implode('، ', $free_str);
    }

    $_SESSION['booking_error'] = $message;
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../../index.php';
redirect($referer);
}

// ====== محاسبه تعداد شب‌ها ======
$date1 = new DateTime($check_in_greg);
$date2 = new DateTime($check_out_greg);
$interval = $date1->diff($date2);
$nights = $interval->days;
$total_price = $nights * $acc['price_per_night'];

$stmt = $pdo->prepare("INSERT INTO reservations (accommodation_id, guest_id, check_in, check_out, total_price, status) VALUES (?, ?, ?, ?, ?, 'pending')");
$stmt->execute([$accommodation_id, $_SESSION['user_id'], $check_in, $check_out, $total_price]);
$reservation_id = $pdo->lastInsertId();

$stmt = $pdo->prepare("INSERT INTO payments (reservation_id, amount, status) VALUES (?, ?, 'pending')");
$stmt->execute([$reservation_id, $total_price]);

redirect("payment.php?reservation_id=$reservation_id");