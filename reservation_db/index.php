<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'config/functions.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");



// ====== دریافت خطا از سشن ======
$register_error = isset($_SESSION['register_error']) ? $_SESSION['register_error'] : '';
unset($_SESSION['register_error']);


// دریافت پارامترهای جستجو
$city_id = isset($_GET['city_id']) ? (int)$_GET['city_id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';
$guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 1;
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : '';

// ====== تشخیص اینکه آیا جستجو انجام شده است ======
$is_search = ($city_id > 0 || !empty($type) || !empty($check_in) || !empty($check_out) || $guests > 1);


if (!empty($check_in)) {
    $check_in = persianToGregorian($check_in);
}
if (!empty($check_out)) {
    $check_out = persianToGregorian($check_out);
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
if (!empty($check_in) && !empty($check_out)) {
    $conditions[] = "a.id NOT IN (
        SELECT accommodation_id FROM reservations 
        WHERE NOT (check_out <= ? OR check_in >= ?)
    )";
    $params[] = $check_in;
    $params[] = $check_out;
}

$whereClause = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

if ($is_search) {
    $sql = "SELECT a.*, c.name as city_name
            FROM accommodations a
            JOIN cities c ON a.city_id = c.id
            $whereClause AND a.is_active = 1
            ORDER BY a.price_per_night ASC";
} else {
    $sql = "SELECT a.*, c.name as city_name
            FROM accommodations a
            JOIN cities c ON a.city_id = c.id
            $whereClause AND a.is_active = 1
            ORDER BY a.price_per_night ASC LIMIT 6";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$accommodations = $stmt->fetchAll();

include 'includes/header.php';
?>
<?php
// ====== دریافت خطای جستجو ======
$search_error = isset($_SESSION['search_error']) ? $_SESSION['search_error'] : '';
unset($_SESSION['search_error']);
?>

<!-- ====== مودال خطای جستجو (با عکس پس‌زمینه) ====== -->
<?php if (!empty($search_error)): ?>
<div class="modal-overlay" id="searchErrorModal" style="
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background:rgba(0,0,0,0.6);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: modalFadeIn 0.4s ease;
    padding: 20px;
">
    <div class="modal-container" style="
        background: rgba(255, 255, 255, 0.26);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        max-width: 550px;
        width: 100%;
        padding: 2rem;
        box-shadow: 0 30px 60px rgba(0,0,0,0.3);
        border: 1px solid rgba(255, 120, 120, 0.69);
        position: relative;
        overflow: hidden;
        direction: rtl;
    ">
        <!-- ====== عکس پس‌زمینه ====== -->
        <div style="
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('<?= BASE_URL ?>/uploads/khone.jpg');
            background-size: cover;
            background-position: center;
            opacity: 0.10;
            z-index: 0;
            border-radius: 28px;
        "></div>

        <!-- ====== محتوا ====== -->
        <div style="position: relative; z-index: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="color: #dc3545; font-weight: 800; font-size: 1.5rem; margin: 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i> خطا در جستجو
                </h3>
                <button onclick="closeSearchErrorModal()" style="background:none;border:none;font-size:2rem;cursor:pointer;color:rgb(255, 156, 103);transition:0.3s;padding:0 8px;line-height:1;">&times;</button>
            </div>

            <div style="background:rgba(255, 177, 151, 0.34);backdrop-filter:blur(4px);border-radius:16px;padding:1.5rem;margin-bottom:1.5rem;border:1px solid rgba(255,255,255,0.3);">
                <p style="color:#1e2a3e;font-size:1rem;line-height:2;margin:0;text-align:right;font-weight:500;"><?= htmlspecialchars($search_error) ?></p>
            </div>

            <button onclick="closeSearchErrorModal()" style="
                background: linear-gradient(135deg, #ff6b35, #ff8a5a);
                border: none;
                border-radius: 50px;
                padding: 0.8rem 2.5rem;
                color: white;
                font-weight: 700;
                font-size: 1rem;
                cursor: pointer;
                transition: 0.3s;
                width: 100%;
                box-shadow: 0 4px 15px rgba(255,107,53,0.3);
            " onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(255,107,53,0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255,107,53,0.3)'">
                متوجه شدم
            </button>
        </div>
    </div>
</div>

<style>
@keyframes modalFadeIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}
@keyframes modalFadeOut {
    from { opacity: 1; transform: scale(1); }
    to { opacity: 0; transform: scale(0.9); }
}
</style>

<script>
// ====== تابع بستن مودال ======
function closeSearchErrorModal() {
    var modal = document.getElementById('searchErrorModal');
    if (modal) {
        modal.style.animation = 'modalFadeOut 0.3s ease forwards';
        setTimeout(function() {
            modal.style.display = 'none';
        }, 300);
    }
}
// ====== بستن با کلیک خارج از مودال ======
document.addEventListener('click', function(e) {
    var modal = document.getElementById('searchErrorModal');
    if (modal && modal.style.display !== 'none') {
        var container = modal.querySelector('.modal-container');
        if (container && !container.contains(e.target)) {
            closeSearchErrorModal();
        }
    }
});

// ====== بستن با کلید ESC ======
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSearchErrorModal();
    }
});

// ====== اطمینان از اینکه تابع در دسترس است (برای onclick) ======
window.closeSearchErrorModal = closeSearchErrorModal;
</script>
<?php endif; ?>

<!-- ====== نمایش توست خطا (در صورت وجود) ====== -->
<?php if (!empty($register_error)): ?>
<div id="registerToast" style="position:fixed;bottom:30px;left:50%;transform:translateX(-50%);z-index:9999;min-width:320px;max-width:450px;border-radius:16px;overflow:hidden;box-shadow:0 15px 50px rgba(0,0,0,0.15);background:#fff5f5;border:1px solid #f5c6cb;direction:rtl;animation:slideUp 0.5s ease forwards;">
    <div style="display:flex;align-items:flex-start;gap:14px;padding:16px 20px;">
        <span style="color:#dc3545;font-size:1.6rem;flex-shrink:0;"><i class="fas fa-exclamation-circle"></i></span>
        <span style="flex:1;font-size:0.95rem;line-height:1.7;font-weight:500;color:#721c24;"><?= htmlspecialchars($register_error) ?></span>
        <button onclick="this.closest('#registerToast').style.display='none'" style="background:none;border:none;font-size:1.6rem;cursor:pointer;color:rgba(0,0,0,0.3);transition:0.3s;padding:0 4px;line-height:1;">&times;</button>
    </div>
    <div style="height:4px;width:100%;background:rgba(0,0,0,0.08);">
        <span style="display:block;height:100%;width:100%;background:#dc3545;animation:progress 6s linear forwards;"></span>
    </div>
</div>
<style>
@keyframes slideUp {
    from { opacity:0; transform:translateX(-50%) translateY(30px) scale(0.95); }
    to { opacity:1; transform:translateX(-50%) translateY(0) scale(1); }
}
@keyframes progress {
    from { width:100%; }
    to { width:0%; }
}
</style>
<script>
setTimeout(function() {
    var toast = document.getElementById('registerToast');
    if (toast) { toast.style.display = 'none'; }
}, 6000);
</script>
<?php endif; ?>


<!-- ====== عنوان نتایج ====== -->
<div class="row mb-4">
    <div class="col">
        <h2 class="fw-bold"><?= $is_search ? '🔍 اقامتگاه مورد نظر' : '🏠 اقامتگاه‌های پیشنهادی' ?></h2>
        <p class="text-muted"><?= count($accommodations) ?> مورد یافت شد</p>
    </div>
</div>

<!-- ====== نمایش نتایج ====== -->
<?php if (count($accommodations) == 0): ?>
    <div class="col-12">
        <div class="alert alert-warning text-center py-5">هیچ اقامتگاهی با این مشخصات یافت نشد.</div>
    </div>
<?php else: ?>

    <?php if ($is_search): ?>
        <!-- ====== حالت جستجو: نمایش لیستی (شبکه‌ای) ====== -->
        <div class="row search-results-row">
            <?php foreach ($accommodations as $acc): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 search-result-item">
                    <div class="card accommodation-card h-100 ">
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
                                <a href="modules/accommodation/single.php?id=<?= $acc['id'] ?>" class="btn btn-sm btn-outline-primary">رزرو <i class="fas fa-arrow-left"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <!-- ====== حالت عادی (بدون جستجو): اسلایدر با فلش‌ها ====== -->
        <div id="accommodationCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
            <div class="carousel-inner">
                <?php
                $chunks = array_chunk($accommodations, 3);
                foreach ($chunks as $index => $chunk):
                ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4 tablet-2cols ">
                            <?php foreach ($chunk as $acc): ?>
                                <div class="col">
                                    <div class="card accommodation-card h-100 mb-4">
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
                                                <a href="modules/accommodation/single.php?id=<?= $acc['id'] ?>" class="btn btn-sm btn-outline-primary">رزرو <i class="fas fa-arrow-left"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- فلش‌های چپ و راست (فقط در حالت عادی) -->
            <?php if (count($chunks) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#accommodationCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">قبلی</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#accommodationCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">بعدی</span>
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php endif; ?>
<!-- ============================================ -->
<!-- بنر تبلیغاتی بزرگ با انیمیشن                  -->
<!-- ============================================ -->
<div class="promo-banner mt-5 mb-4">
    <div class="promo-content">
        <div class="promo-image">
            <img src="<?= BASE_URL ?>/uploads/baner.jpg" alt="تخفیف ویژه اقامتگاه‌ها" class="promo-img">
        </div>
        <div class="promo-text">
            <h3 class="promo-title">🎉 تخفیف ویژه تابستانه</h3>
            <p class="promo-description">با کد تخفیف <strong>SUMMER1404</strong> تا ۳۰٪ تخفیف بگیرید!</p>
           <!--<?= BASE_URL ?>/modules/reservation/search.php?discount=summer" class="btn btn-promo">مشاهده پیشنهادها</a> -->
        </div>
    </div>
</div>


<?php include 'includes/footer.php'; ?>