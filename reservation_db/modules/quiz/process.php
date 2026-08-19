<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../../config/database.php';
require_once '../../config/functions.php';

// جلوگیری از کش شدن صفحه
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include '../../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    redirect('start.php');
}

$user_id = isLoggedIn() ? $_SESSION['user_id'] : 0;
$session_id = session_id();

// ====== ذخیره پاسخ‌ها (و پاک کردن قبلی‌ها) ======
if ($user_id) {
    $pdo->prepare("DELETE FROM user_quiz_answers WHERE user_id = ?")->execute([$user_id]);
} else {
    $pdo->prepare("DELETE FROM user_quiz_answers WHERE session_id = ?")->execute([$session_id]);
}

foreach ($_POST as $key => $value) {
    if (strpos($key, 'q') === 0) {
        $question_id = (int)substr($key, 1);
        if (is_array($value)) {
            $answer_value = implode(',', $value);
            $answer_text = $answer_value;
        } else {
            $answer_value = $value;
            $answer_text = $value;
        }
        $stmt = $pdo->prepare("INSERT INTO user_quiz_answers (user_id, session_id, question_id, answer_value, answer_text) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $session_id, $question_id, $answer_value, $answer_text]);
    }
}

// ====== دریافت پاسخ‌های کلیدی ======

// 1. تعداد مسافران (سوال 1)
$stmt = $pdo->prepare("SELECT answer_value FROM user_quiz_answers WHERE (user_id=? OR session_id=?) AND question_id=1");
$stmt->execute([$user_id, $session_id]);
$guest_answer = $stmt->fetchColumn();
$guest_count = 1;
if (preg_match('/(\d+)\s*تا\s*(\d+)/', $guest_answer, $matches)) {
    $guest_count = (int)$matches[1];
} elseif (preg_match('/(\d+)\s*نفر\s*به\s*بالا/', $guest_answer, $matches)) {
    $guest_count = (int)$matches[1];
} elseif (preg_match('/(\d+)/', $guest_answer, $matches)) {
    $guest_count = (int)$matches[1];
}
if (strpos($guest_answer, 'بیش از 10') !== false || strpos($guest_answer, '10 نفر به بالا') !== false) {
    $guest_count = 10;
}

// 2. بودجه (سوال 6)
$stmt = $pdo->prepare("SELECT answer_value FROM user_quiz_answers WHERE (user_id=? OR session_id=?) AND question_id=6");
$stmt->execute([$user_id, $session_id]);
$budget_str = $stmt->fetchColumn();
$budget_min = 0; $budget_max = 10000000;
if (strpos($budget_str, '1 - 2 میلیون') !== false) { $budget_min = 1000000; $budget_max = 2000000; }
elseif (strpos($budget_str, '2 - 3 میلیون') !== false) { $budget_min = 2000000; $budget_max = 3000000; }
elseif (strpos($budget_str, '3 - 5 میلیون') !== false) { $budget_min = 3000000; $budget_max = 5000000; }
elseif (strpos($budget_str, 'کمتر از 500 هزار') !== false) { $budget_min = 0; $budget_max = 500000; }
elseif (strpos($budget_str, '500 هزار - 1 میلیون') !== false) { $budget_min = 500000; $budget_max = 1000000; }
elseif (strpos($budget_str, 'بیش از 5 میلیون') !== false) { $budget_min = 5000000; $budget_max = 100000000; }

// 3. امکانات (سوال 9)
$stmt = $pdo->prepare("SELECT answer_value FROM user_quiz_answers WHERE (user_id=? OR session_id=?) AND question_id=9");
$stmt->execute([$user_id, $session_id]);
$amenities_str = $stmt->fetchColumn();
$selected_amenities = array_map('trim', explode(',', $amenities_str));
if (empty($selected_amenities) || $selected_amenities[0] == '') {
    $selected_amenities = [];
}

// 4. نوع اقامتگاه (سوال 16)
// 4. نوع اقامتگاه (سوال 16)
$stmt = $pdo->prepare("SELECT answer_value FROM user_quiz_answers WHERE (user_id=? OR session_id=?) AND question_id=16");
$stmt->execute([$user_id, $session_id]);
$type_answer = $stmt->fetchColumn();

// ====== نگاشت گزینه‌های پرسشنامه به مقادیر واقعی دیتابیس ======
$type_mapping = [
    'ویلا' => 'ویلا',
    'سوئیت' => 'سوییت',
    'سوئیت لوکس' => 'سوییت',   // ← نگاشت به سوییت
    'ویلا و سوئیت لوکس' => ['ویلا', 'سوییت'], // ← هر دو را شامل می‌شود
    'کلبه' => 'کلبه',
    'خانه' => 'خانه',
    'آپارتمان' => 'آپارتمان'
];

$selected_types = [];
if (!empty($type_answer) && $type_answer != 'همه موارد') {
    // اگر پاسخ شامل 'و' بود، آن را به آرایه تبدیل کن
    if (strpos($type_answer, ' و ') !== false) {
        $parts = explode(' و ', $type_answer);
        foreach ($parts as $part) {
            if (isset($type_mapping[$part])) {
                $mapped = $type_mapping[$part];
                if (is_array($mapped)) {
                    $selected_types = array_merge($selected_types, $mapped);
                } else {
                    $selected_types[] = $mapped;
                }
            }
        }
    } else {
        // یک مقدار
        if (isset($type_mapping[$type_answer])) {
            $mapped = $type_mapping[$type_answer];
            if (is_array($mapped)) {
                $selected_types = $mapped;
            } else {
                $selected_types = [$mapped];
            }
        }
    }
}
// حذف مقادیر تکراری
$selected_types = array_unique($selected_types);
// اگر 'همه موارد' انتخاب شده باشد، $selected_types خالی می‌ماند

// 5. شهرهای مورد نظر (سوال 5)
$stmt = $pdo->prepare("SELECT answer_value FROM user_quiz_answers WHERE (user_id=? OR session_id=?) AND question_id=5");
$stmt->execute([$user_id, $session_id]);
$city_answer = $stmt->fetchColumn();
$selected_city_names = array_map('trim', explode(',', $city_answer));
if (empty($selected_city_names) || $selected_city_names[0] == '' || $city_answer == 'هیچکدام (هر دو)') {
    $selected_city_names = [];
}
// ====== تبدیل نام شهرها به ID ======
$city_ids = [];
if (!empty($selected_city_names)) {
    $placeholders = implode(',', array_fill(0, count($selected_city_names), '?'));
    $stmt = $pdo->prepare("SELECT id FROM cities WHERE name IN ($placeholders)");
    $stmt->execute($selected_city_names);
    $city_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// ====== فیلترهای سخت ======
$filters = [];
$filter_params = [];

if (!empty($city_ids)) {
    $filters[] = "a.city_id IN (" . implode(',', array_fill(0, count($city_ids), '?')) . ")";
    $filter_params = array_merge($filter_params, $city_ids);
}

if (!empty($selected_types)) {
    $filters[] = "a.accommodation_type IN (" . implode(',', array_fill(0, count($selected_types), '?')) . ")";
    $filter_params = array_merge($filter_params, $selected_types);
}

$price_limit = $budget_max * 3; // ۳ برابر بودجه (به جای ۱.۵)
$min_capacity = max(1, round($guest_count * 0.3)); // ۳۰٪ تعداد مهمان (به جای ۷۰٪)

$min_capacity = max(1, round($guest_count * 0.7));
$filters[] = "a.max_guests >= ?";
$filter_params[] = $min_capacity;

$where = count($filters) > 0 ? "WHERE " . implode(" AND ", $filters) : "";
$sql = "SELECT a.*, c.name as city_name 
        FROM accommodations a
        JOIN cities c ON a.city_id = c.id
        $where
        AND a.is_active = 1";



// ====== اجرای کوئری ======
$stmt = $pdo->prepare($sql);
$stmt->execute($filter_params);
$accommodations = $stmt->fetchAll();
// ====== ادامه کد (امتیازدهی و نمایش) ======

// ====== امتیازدهی ======
$scored = [];
foreach ($accommodations as $acc) {
    $score = 0;
    $max_score = 100;
    
    // 1. شهر (وزن 40)
    if (!empty($city_ids) && in_array($acc['city_id'], $city_ids)) {
        $score += 40;
    } elseif (!empty($city_ids)) {
        $score += 0;
    } else {
        $score += 20;
    }
    
    // 2. بودجه (وزن 30)
    $price = $acc['price_per_night'];
    if ($price >= $budget_min && $price <= $budget_max) $score += 30;
    elseif ($price < $budget_min) $score += 20;
    elseif ($price <= $budget_max * 1.2) $score += 15;
    elseif ($price <= $budget_max * 1.5) $score += 8;
    else $score += 0;
    
    // 3. ظرفیت (وزن 20)
    $cap = $acc['max_guests'];
    if ($cap >= $guest_count) {
        if ($cap <= $guest_count + 2) $score += 20;
        elseif ($cap <= $guest_count + 5) $score += 15;
        elseif ($cap <= $guest_count + 10) $score += 10;
        else $score += 5;
    } else {
        if ($cap >= $guest_count * 0.7) $score += 8;
        else $score += 0;
    }
    
    // 4. نوع اقامتگاه (وزن 10)
    $acc_type = $acc['accommodation_type'];
    if (!empty($selected_types) && in_array($acc_type, $selected_types)) {
        $score += 10;
    } elseif (!empty($selected_types)) {
        $score += 0;
    } else {
        $score += 3;
    }
    
    $percent = round(($score / $max_score) * 100);
    $acc['score'] = $score;
    $acc['percent'] = min($percent, 100);
    $scored[] = $acc;
}

// ====== مرتب‌سازی ======
usort($scored, function($a, $b) use ($city_ids, $budget_min, $guest_count) {
    $city_match_a = empty($city_ids) || in_array($a['city_id'], $city_ids);
    $city_match_b = empty($city_ids) || in_array($b['city_id'], $city_ids);
    if ($city_match_a != $city_match_b) {
        return $city_match_a ? -1 : 1;
    }
    $price_diff_a = abs($a['price_per_night'] - $budget_min);
    $price_diff_b = abs($b['price_per_night'] - $budget_min);
    if ($price_diff_a != $price_diff_b) {
        return $price_diff_a - $price_diff_b;
    }
    $cap_diff_a = abs($a['max_guests'] - $guest_count);
    $cap_diff_b = abs($b['max_guests'] - $guest_count);
    return $cap_diff_a - $cap_diff_b;
});

// ====== نمایش ۵ نتیجه برتر ======
$recommendations = array_slice($scored, 0, 5);
?>
<h3 class="text-center mb-4">نتایج پیشنهادی بر اساس سلیقه شما</h3>

<?php if (count($recommendations) == 0): ?>
    <div class="alert alert-warning text-center py-5">
        <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
<a href="quiz_form.php" class="btn btn-primary mt-2">بازگشت به پرسشنامه</a>        <p>لطفاً فیلترهای خود را تغییر دهید (مثلاً تعداد مسافران یا بودجه را کمتر کنید).</p>
        <a href="<?= BASE_URL ?>/modules/quiz/quiz_form.php" class="btn btn-primary mt-2">بازگشت به پرسشنامه</a>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($recommendations as $rec): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($rec['title']) ?></h5>
                        <p class="card-text text-muted"><i class="fas fa-map-marker-alt"></i> <?= $rec['city_name'] ?></p>
                        <p class="card-text"><strong><?= number_format($rec['price_per_night']) ?> تومان</strong> / شب</p>
                        <p class="card-text"><i class="fas fa-users"></i> ظرفیت: <?= $rec['max_guests'] ?> نفر</p>
                        
                        <div class="progress mb-2" style="height: 25px; direction: ltr;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: <?= $rec['percent'] ?>%;" 
                                 aria-valuenow="<?= $rec['percent'] ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?= $rec['percent'] ?>% تطابق
                            </div>
                        </div>
                        
                        <a href="../accommodation/single.php?id=<?= $rec['id'] ?>" class="btn btn-primary btn-sm">انتخاب و رزرو</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="text-center mt-3">
<a href="<?= BASE_URL ?>/modules/quiz/quiz.php" class="btn btn-outline-secondary">🔄 بازگشت به پرسشنامه</a>
<?php include '../../includes/footer.php'; ?>

