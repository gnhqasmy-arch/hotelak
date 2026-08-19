<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once 'config/database.php';
require_once 'config/functions.php';



// ====== دریافت پارامترهای جستجو ======
$city_id = isset($_GET['city_id']) ? (int)$_GET['city_id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 1;
$check_in = isset($_GET['check_in']) ? trim($_GET['check_in']) : '';
$check_out = isset($_GET['check_out']) ? trim($_GET['check_out']) : '';
// ====== دریافت پارامتر مرتب‌سازی ======
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'price_asc'; // پیش‌فرض: ارزان‌ترین

// ====== تبدیل اعداد فارسی به انگلیسی ======
$persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
$english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
$check_in = str_replace($persian, $english, $check_in);
$check_out = str_replace($persian, $english, $check_out);

// ====== اعتبارسنجی تاریخ‌ها (فقط در صورتی که هر دو پر باشند) ======
if (!empty($check_in) && !empty($check_out)) {
    // 1. بررسی فرمت (YYYY/MM/DD)
    if (!preg_match('/^\d{4}\/\d{1,2}\/\d{1,2}$/', $check_in) || !preg_match('/^\d{4}\/\d{1,2}\/\d{1,2}$/', $check_out)) {
        $_SESSION['search_error'] = 'فرمت تاریخ نامعتبر است. لطفاً از تقویم استفاده کنید.';
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
        redirect($referer);
    }

    // 2. بررسی اینکه تاریخ خروج بعد از ورود باشد (مقایسه رشته‌ای)
    if ($check_in >= $check_out) {
        $_SESSION['search_error'] = '⚠️ تاریخ خروج باید بعد از تاریخ ورود باشد.';
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
        redirect($referer);
    }
}
// ====== شرط‌های جستجو ======
$conditions = [];
$params = [];

if ($city_id > 0) {
    $conditions[] = "a.city_id = ?";
    $params[] = $city_id;
}
if (!empty($type)) {
    $conditions[] = "a.accommodation_type = ?";
    $params[] = $type;
}
if ($guests > 0) {
    $conditions[] = "a.max_guests >= ?";
    $params[] = $guests;
}

// ====== شرط تاریخ (مقایسه مستقیم رشته‌ای با تاریخ شمسی) ======
if (!empty($check_in) && !empty($check_out)) {
    $conditions[] = "a.id NOT IN (
        SELECT accommodation_id FROM reservations 
        WHERE (check_in < ? AND check_out > ?)
    )";
    $params[] = $check_out;
    $params[] = $check_in;
}
switch ($sort) {
    case 'price_asc':
        $order_by = "a.price_per_night ASC";
        break;
    case 'price_desc':
        $order_by = "a.price_per_night DESC";
        break;
    default:
        $order_by = "a.price_per_night ASC";
}


$whereClause = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
$sql = "SELECT a.*, c.name as city_name
        FROM accommodations a
        JOIN cities c ON a.city_id = c.id
        $whereClause AND a.is_active = 1
        ORDER BY $order_by";


$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$accommodations = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- ====== صفحه نتایج جستجو ====== -->
<div class="container py-4">
   <!-- ====== عنوان نتایج و مرتب‌سازی ====== -->
<!-- ====== عنوان نتایج و مرتب‌سازی حرفه‌ای ====== -->
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="fw-bold">🔍 نتایج جستجو</h2>
        <p class="text-muted"><?= count($accommodations) ?> اقامتگاه یافت شد</p>
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-right"></i> بازگشت به صفحه اصلی
            </a>
    </div>
    <div class="col-md-6 text-md-end">
        <div class="sort-filter-wrapper">
            <button class="sort-filter-btn" onclick="toggleSortDropdown()">
                <i class="fas fa-arrow-up-wide-short"></i>
                <span>مرتب‌سازی</span>
                <i class="fas fa-chevron-down" style="font-size: 0.6rem; margin-right: 6px;"></i>
            </button>
            <div class="sort-filter-dropdown" id="sortDropdown">
                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'price_asc'])) ?>" 
                   class="sort-option <?= (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'active' : '' ?>">
                    <i class="fas fa-arrow-up"></i> ارزان‌ترین
                </a>
                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'price_desc'])) ?>" 
                   class="sort-option <?= (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'active' : '' ?>">
                    <i class="fas fa-arrow-down"></i> گران‌ترین
                </a>
            </div>
        </div>
    </div>
</div>

    <?php if (count($accommodations) == 0): ?>
        <div class="alert alert-warning text-center py-5">
            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
            <h4>هیچ اقامتگاهی با این مشخصات یافت نشد.</h4>
            <p>لطفاً فیلترهای جستجو را تغییر دهید.</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($accommodations as $acc): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-4">
                    <div class="card accommodation-card h-100">
                        <?php
                        $imgStmt = $pdo->prepare("SELECT image_url FROM accommodation_gallery WHERE accommodation_id = ? AND is_cover = 1 LIMIT 1");
                        $imgStmt->execute([$acc['id']]);
                        $cover = $imgStmt->fetch();
                        $imgSrc = $cover ? BASE_URL . $cover['image_url'] : BASE_URL . '/uploads/default.jpg';
                        ?>
                        <img src="<?= $imgSrc ?>" class="card-img-top" height="200" style="object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($acc['title']) ?></h5>
                            <p class="card-text text-muted"><i class="fas fa-map-marker-alt"></i> <?= $acc['city_name'] ?></p>
                            <p class="card-text"><i class="fas fa-users"></i> ظرفیت: <?= $acc['max_guests'] ?> نفر</p>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="price-badge"><?= number_format($acc['price_per_night']) ?> تومان</span>
<a href="modules/accommodation/single.php?id=<?= $acc['id'] ?>" class="btn btn-sm btn-outline-primary">رزرو</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

