<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../../config/database.php';
require_once '../../config/functions.php';

// ====== مخفی کردن کادر جستجو در صفحه جزئیات اقامتگاه ======
$hide_search = true;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('../../index.php');
}




if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('../../index.php');
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT a.*, c.name as city_name, c.province 
                        FROM accommodations a
                        JOIN cities c ON a.city_id = c.id
                        WHERE a.id = ? AND a.is_active = 1");
$stmt->execute([$id]);
$accommodation = $stmt->fetch();

if (!$accommodation) {
    redirect('../../index.php');
}

$imgStmt = $pdo->prepare("SELECT image_url, is_cover, alt_text FROM accommodation_gallery WHERE accommodation_id = ? ORDER BY is_cover DESC, sort_order ASC");
$imgStmt->execute([$id]);
$gallery = $imgStmt->fetchAll();
if (empty($gallery)) {
    $gallery = [['image_url' => '/uploads/default.jpg', 'is_cover' => 1, 'alt_text' => 'بدون عکس']];
}

$amenStmt = $pdo->prepare("SELECT am.name FROM accommodation_amenities aa JOIN amenities am ON aa.amenity_id = am.id WHERE aa.accommodation_id = ?");
$amenStmt->execute([$id]);
$amenities = $amenStmt->fetchAll(PDO::FETCH_COLUMN);

include '../../includes/header.php';

// ====== دریافت پیام‌های سشن ======
$booking_error = '';
if (isset($_SESSION['booking_error'])) {
    $booking_error = $_SESSION['booking_error'];
    unset($_SESSION['booking_error']);
}

$login_message = '';
if (isset($_SESSION['login_message'])) {
    $login_message = $_SESSION['login_message'];
    unset($_SESSION['login_message']);
}

// ====== دریافت تاریخ‌های رزرو شده ======
$reserved_dates = [];
$stmt = $pdo->prepare("SELECT check_in, check_out FROM reservations WHERE accommodation_id = ? AND status IN ('pending', 'confirmed')");
$stmt->execute([$id]);
$reservations = $stmt->fetchAll();

foreach ($reservations as $res) {
    $start = strtotime(str_replace('/', '-', $res['check_in']));
    $end = strtotime(str_replace('/', '-', $res['check_out']));
    while ($start < $end) {
        $reserved_dates[] = date('Y/m/d', $start);
        $start = strtotime('+1 day', $start);
    }
}
$reserved_dates = array_unique($reserved_dates);
$reserved_dates_json = json_encode($reserved_dates);
?>

<style>
/* ====== تمام استایل‌های شما ====== */
body {
    background-image: url('<?= BASE_URL ?>/uploads/3d-rendering.jpg');
    background-size: cover;
    background-attachment: fixed;
    background-position: center;
    position: relative;
}
body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.3);
    z-index: 0;
    pointer-events: none;
}
.single-container {
    position: relative;
    z-index: 1;
}
.fade-up {
    opacity: 0;
    transform: translateY(30px);
    animation: fadeUp 0.7s ease forwards;
}
.fade-up:nth-child(1) { animation-delay: 0.1s; }
.fade-up:nth-child(2) { animation-delay: 0.2s; }
.fade-up:nth-child(3) { animation-delay: 0.3s; }
.fade-up:nth-child(4) { animation-delay: 0.4s; }
@keyframes fadeUp {
    to { opacity: 1; transform: translateY(0); }
}
.gallery-section {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}
.gallery-main {
    flex: 0 0 65%;
    border-radius: 20px;
    overflow: hidden;
    cursor: pointer;
    position: relative;
    height: 420px;
    background: rgba(30,42,62,0.5);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.15);
}
.gallery-main img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.gallery-thumbs {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-width: 150px;
    max-height: 420px;
}
.gallery-thumbs .thumb-item {
    border-radius: 16px;
    overflow: hidden;
    cursor: pointer;
    flex: 1;
    border: 3px solid rgba(255,255,255,0.1);
    transition: all 0.3s ease;
    min-height: 0;
    background: rgba(0,0,0,0.2);
}
.gallery-thumbs .thumb-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.gallery-thumbs .thumb-item:hover,
.gallery-thumbs .thumb-item.active-thumb {
    border-color: #ffb07c;
    box-shadow: 0 0 25px rgba(255,176,124,0.3);
}
.gallery-thumbs .thumb-item:last-child .more-badge {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    font-weight: 700;
    border-radius: 16px;
}
.lightbox-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.92);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 40px;
    flex-direction: column;
}
.lightbox-overlay.active { display: flex; }
.lightbox-content {
    max-width: 80%;
    max-height: 80vh;
    border-radius: 16px;
    object-fit: contain;
    animation: lightboxFade 0.4s ease;
}
@keyframes lightboxFade {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}
.lightbox-close {
    position: absolute;
    top: 25px;
    right: 35px;
    color: white;
    font-size: 2.5rem;
    cursor: pointer;
    transition: 0.3s;
    background: none;
    border: none;
}
.lightbox-close:hover { transform: rotate(90deg); color: #ffb07c; }
.lightbox-counter {
    color: rgba(255,255,255,0.7);
    font-size: 1rem;
    margin-top: 15px;
}
.lightbox-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255,166,121,0.23);
    backdrop-filter: blur(8px);
    color: white;
    border: 1px solid rgba(255,255,255,0.2);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    font-size: 1.8rem;
    cursor: pointer;
    transition: 0.3s;
}
.lightbox-nav:hover { background: #ffb07c; color: #1e2a3e; }
.lightbox-nav.prev { left: 30px; }
.lightbox-nav.next { right: 30px; }
.glass-card {
    background: rgba(255, 206, 193, 0.93);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 24px;
    padding: 1.8rem 2rem;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    color: #fff;
}
.glass-card .text-muted {
    color: rgba(255,255,255,0.8) !important;
}
.price-box {
    background: rgba(255, 145, 105, 0.54);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,176,124,0.4);
    border-radius: 18px;
    padding: 1.2rem 1.5rem;
    text-align: center;
    box-shadow: 0 8px 25px rgba(255,176,124,0.2);
}
.price-box .price {
    font-size: 2.2rem;
    font-weight: 800;
    color: #fff;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
}
.price-box .per-night {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.8);
}
.accommodation-title {
    font-size: 2rem;
    font-weight: 800;
    color: #ffffff;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
}
.accommodation-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    color: rgba(255,255,255,0.8);
    font-size: 0.95rem;
}
.accommodation-meta i {
    color: #ffb07c;
    width: 20px;
}
.amenity-tag {
    display: inline-block;
    background: rgba(244,113,113,0.57);
    backdrop-filter: blur(4px);
    padding: 5px 16px;
    border-radius: 30px;
    font-size: 0.85rem;
    color: #fff;
    margin: 3px 5px 3px 0;
    transition: 0.3s;
    border: 1px solid rgba(255,255,255,0.05);
}
.amenity-tag:hover {
    background: rgba(255,176,124,0.3);
    color: #fff;
    transform: scale(1.05);
}
.booking-card {
    background: rgba(255, 190, 190, 0.78);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 24px;
    padding: 1.8rem;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    position: sticky;
    top: 100px;
    color: #fff;
}
.booking-card .form-control {
    border-radius: 14px;
    padding: 0.7rem 1rem;
    border: 1px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.08);
    color: #fff;
}
.booking-card .form-control:focus {
    border-color: #ffb07c;
    box-shadow: 0 0 0 4px rgba(255,176,124,0.12);
    background: rgba(255,255,255,0.15);
}
.booking-card .form-control::placeholder {
    color: rgba(255,255,255,0.6);
}
.booking-card label {
    color: rgba(255,255,255,0.9);
}
.booking-card .text-muted {
    color: rgba(255,255,255,0.6) !important;
}
.btn-reserve {
    background: linear-gradient(135deg, #ffb08b, #ff9f79);
    border: none;
    border-radius: 50px;
    padding: 0.8rem;
    font-weight: 700;
    color: white;
    transition: 0.3s;
    box-shadow: 0 4px 20px rgba(255,176,124,0.3);
}
.btn-reserve:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 35px rgba(255,176,124,0.5);
    color: white;
}
@media (max-width: 992px) {
    .gallery-main { flex: 1 1 100%; height: 300px; }
    .gallery-thumbs { flex-direction: row; flex-wrap: wrap; max-height: none; }
    .gallery-thumbs .thumb-item { flex: 1 1 30%; height: 80px; }
    .lightbox-content { max-width: 95%; }
    .lightbox-nav { width: 40px; height: 40px; font-size: 1.2rem; }
    .lightbox-nav.prev { left: 10px; }
    .lightbox-nav.next { right: 10px; }
}
@media (max-width: 576px) {
    .gallery-thumbs .thumb-item { flex: 1 1 45%; height: 70px; }
    .accommodation-title { font-size: 1.4rem; }
    .glass-card { padding: 1.2rem; }
    .booking-card { position: static; margin-top: 20px; }
    .price-box .price { font-size: 1.6rem; }
}
</style>

<!-- ====== مودال خطای رزرو ====== -->
<?php if (!empty($booking_error)): ?>
<div class="modal-overlay" id="bookingErrorModal" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);backdrop-filter:blur(8px);display:flex;align-items:center;justify-content:center;z-index:9999;animation:modalFadeIn 0.4s ease;padding:20px;">
    <div class="modal-container" style="background:rgba(255, 255, 255, 0.26);backdrop-filter:blur(20px);border-radius:28px;max-width:550px;width:100%;padding:2rem;box-shadow:0 30px 60px rgba(0,0,0,0.3);border:1px solid rgba(255, 120, 120, 0.69);position:relative;overflow:hidden;direction:rtl;">
        <div style="position:absolute;top:0;left:0;width:100%;height:100%;background-image:url('<?= BASE_URL ?>/uploads/khone.jpg');background-size:cover;background-position:center;opacity:0.10;z-index:0;border-radius:28px;"></div>
        <div style="position:relative;z-index:1;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
                <h3 style="color:#dc3545;font-weight:800;font-size:1.5rem;margin:0;display:flex;align-items:center;gap:10px;"><i class="fas fa-exclamation-triangle" style="color:#ffc107;"></i> خطا در رزرو</h3>
                <button onclick="closeBookingModal()" style="background:none;border:none;font-size:2rem;cursor:pointer;color:rgb(255, 156, 103);transition:0.3s;padding:0 8px;">×</button>
            </div>
            <div style="background:rgba(255, 177, 151, 0.34);backdrop-filter:blur(4px);border-radius:16px;padding:1.5rem;margin-bottom:1.5rem;border:1px solid rgba(255,255,255,0.3);">
                <p style="color:#1e2a3e;font-size:1rem;line-height:2;margin:0;white-space:pre-line;text-align:right;font-weight:500;"><?= nl2br(htmlspecialchars($booking_error)) ?></p>
            </div>
            <button onclick="closeBookingModal()" style="background:linear-gradient(135deg,#ff6b35,#ff8a5a);border:none;border-radius:50px;padding:0.8rem 2.5rem;color:white;font-weight:700;font-size:1rem;cursor:pointer;transition:0.3s;width:100%;box-shadow:0 4px 15px rgba(255,107,53,0.3);"> متوجه شدم</button>
        </div>
    </div>
</div>
<style>
@keyframes modalFadeIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}
</style>
<?php endif; ?>

<!-- ====== توست لاگین ====== -->
<?php if (!empty($login_message)): ?>
<div id="loginToast" class="custom-toast warning-toast">
    <div class="toast-content">
        <i class="fas fa-exclamation-triangle"></i>
        <span class="toast-message"><?= htmlspecialchars($login_message) ?></span>
        <button class="toast-close" onclick="closeToast('loginToast')">&times;</button>
    </div>
    <div class="toast-progress"></div>
</div>
<?php endif; ?>

<!-- ====== محتوای اصلی ====== -->
<div class="single-container container py-4">
    <!-- گالری -->
    <div class="gallery-section fade-up">
        <div class="gallery-main" onclick="openLightbox(0)">
            <img src="<?= BASE_URL . $gallery[0]['image_url'] ?>" id="mainGalleryImg" alt="<?= htmlspecialchars($gallery[0]['alt_text']) ?>">
        </div>
        <div class="gallery-thumbs" id="thumbnails">
            <?php foreach ($gallery as $index => $img): ?>
                <div class="thumb-item <?= $index === 0 ? 'active-thumb' : '' ?>" onclick="changeMainImage(<?= $index ?>)">
                    <img src="<?= BASE_URL . $img['image_url'] ?>" alt="<?= htmlspecialchars($img['alt_text']) ?>">
                    <?php if ($index === 3 && count($gallery) > 4): ?>
                        <div class="more-badge">+<?= count($gallery) - 3 ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Lightbox -->
    <div class="lightbox-overlay" id="lightboxOverlay" onclick="closeLightbox(event)">
        <button class="lightbox-close" onclick="closeLightbox()">✕</button>
        <button class="lightbox-nav prev" onclick="event.stopPropagation(); changeLightbox(-1)">❮</button>
        <button class="lightbox-nav next" onclick="event.stopPropagation(); changeLightbox(1)">❯</button>
        <img class="lightbox-content" id="lightboxImg" src="">
        <div class="lightbox-counter" id="lightboxCounter">1 / <?= count($gallery) ?></div>
    </div>

    <!-- اطلاعات و رزرو -->
    <div class="row g-4 mt-3">
        <div class="col-lg-8 fade-up">
            <div class="glass-card">
                <h1 class="accommodation-title"><?= htmlspecialchars($accommodation['title']) ?></h1>
                <div class="accommodation-meta">
                    <span><i class="fas fa-map-marker-alt"></i> <?= $accommodation['city_name'] ?>, <?= $accommodation['province'] ?></span>
                    <span><i class="fas fa-users"></i> <?= $accommodation['max_guests'] ?> نفر</span>
                    <span><i class="fas fa-bed"></i> <?= $accommodation['bedrooms'] ?> اتاق</span>
                    <span><i class="fas fa-bath"></i> <?= $accommodation['bathrooms'] ?> سرویس</span>
                </div>
                <div class="mt-4">
                    <h5 class="fw-bold">📋 توضیحات</h5>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($accommodation['description'])) ?></p>
                </div>
                <div class="mt-3">
                    <h5 class="fw-bold">🔧 امکانات</h5>
                    <?php foreach ($amenities as $amenity): ?>
                        <span class="amenity-tag"><?= htmlspecialchars($amenity) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4 fade-up">
            <div class="booking-card">
                <div class="price-box mb-3">
                    <div class="price"><?= number_format($accommodation['price_per_night']) ?> تومان</div>
                    <div class="per-night">هر شب</div>
                </div>
                <form action="<?= BASE_URL ?>/modules/reservation/book.php" method="POST" id="bookingForm">
                    <input type="hidden" name="accommodation_id" value="<?= $accommodation['id'] ?>">
                    <div class="mb-3">
                        <label class="fw-bold">📅 تاریخ ورود</label>
                        <input type="text" name="check_in" class="form-control datepicker" placeholder="مثلاً ۱۴۰۴/۰۴/۲۰" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">📅 تاریخ خروج</label>
                        <input type="text" name="check_out" class="form-control datepicker" placeholder="مثلاً ۱۴۰۴/۰۴/۲۵" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">👤 تعداد مهمان</label>
                        <input type="number" name="guests" id="guestCount" class="form-control" min="1" max="<?= $accommodation['max_guests'] ?>" value="2" required>
                        <small class="text-muted">حداکثر ظرفیت: <?= $accommodation['max_guests'] ?> نفر</small>
                    </div>
                    <button type="submit" class="btn btn-reserve w-100">
                        <i class="fas fa-check-circle"></i> رزرو اقامتگاه
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// ====== متغیرها ======
const reservedDates = <?= $reserved_dates_json ?>;
const galleryImages = <?= json_encode(array_column($gallery, 'image_url')) ?>;
const totalImages = galleryImages.length;
let currentLightboxIndex = 0;

// ====== گالری ======
function changeMainImage(index) {
    const mainImg = document.getElementById('mainGalleryImg');
    mainImg.src = '<?= BASE_URL ?>' + galleryImages[index];
    document.querySelectorAll('.thumb-item').forEach((el, i) => {
        el.classList.toggle('active-thumb', i === index);
    });
}

function openLightbox(index) {
    currentLightboxIndex = index;
    document.getElementById('lightboxImg').src = '<?= BASE_URL ?>' + galleryImages[index];
    document.getElementById('lightboxCounter').textContent = (index + 1) + ' / ' + totalImages;
    document.getElementById('lightboxOverlay').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeLightbox(e) {
    if (e && e.target !== e.currentTarget) return;
    document.getElementById('lightboxOverlay').classList.remove('active');
    document.body.style.overflow = 'auto';
}

function changeLightbox(direction) {
    currentLightboxIndex = (currentLightboxIndex + direction + totalImages) % totalImages;
    document.getElementById('lightboxImg').src = '<?= BASE_URL ?>' + galleryImages[currentLightboxIndex];
    document.getElementById('lightboxCounter').textContent = (currentLightboxIndex + 1) + ' / ' + totalImages;
}

// ====== اعتبارسنجی تعداد مهمان ======
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    const maxGuests = <?= $accommodation['max_guests'] ?>;
    const guestInput = document.getElementById('guestCount');
    const val = parseInt(guestInput.value);
    if (val > maxGuests) {
        alert('تعداد مهمان نمی‌تواند بیشتر از ' + maxGuests + ' نفر باشد.');
        e.preventDefault();
        return false;
    }
});

// ====== تقویم شمسی ======
function initDatepickers() {
    if (typeof $ !== 'undefined' && typeof $.fn.persianDatepicker !== 'undefined') {
        var today = new persianDate().format('YYYY/MM/DD');
        $('.datepicker').each(function() {
            if ($(this).data('persianDatepicker')) return;
            var isCheckIn = $(this).attr('name') === 'check_in';
            var isCheckOut = $(this).attr('name') === 'check_out';
            var options = {
                format: 'YYYY/MM/DD',
                autoClose: true,
                initialValue: false,
                calendar: { type: 'persian' },
                showGregorian: false,
                showTodayButton: true,
                closeAfterSelect: true,
                onSelect: function() {
                    $(this).persianDatepicker('hide');
                    if (isCheckIn) {
                        var selectedDate = $(this).val();
                        $('input[name="check_out"]').data('persianDatepicker').options.minDate = selectedDate;
                    }
                },
                onShow: function() {
                    setTimeout(function() {
                        $('.persian-datepicker-body .day').each(function() {
                            var dayEl = $(this);
                            var dateStr = dayEl.attr('data-date');
                            if (dateStr && reservedDates.indexOf(dateStr) !== -1) {
                                dayEl.addClass('disabled');
                                dayEl.css('opacity', '0.3');
                                dayEl.css('pointer-events', 'none');
                            }
                        });
                    }, 100);
                }
            };
            if (isCheckIn) options.minDate = today;
            if (isCheckOut) {
                var checkInVal = $('input[name="check_in"]').val();
                options.minDate = checkInVal && checkInVal.length > 0 ? checkInVal : today;
            }
            $(this).persianDatepicker(options);
            $(this).on('click', function() {
                $(this).persianDatepicker('show');
            });
        });
        $('input[name="check_in"]').on('change', function() {
            var checkInVal = $(this).val();
            if (checkInVal && checkInVal.length > 0) {
                var picker = $('input[name="check_out"]').data('persianDatepicker');
                if (picker) {
                    picker.options.minDate = checkInVal;
                    var checkOutVal = $('input[name="check_out"]').val();
                    if (checkOutVal && checkOutVal < checkInVal) {
                        $('input[name="check_out"]').val('');
                    }
                }
            }
        });
        console.log('✅ تقویم شمسی فعال شد.');
    } else {
        console.warn('⚠️ کتابخانه persian-datepicker یافت نشد.');
        document.querySelectorAll('.datepicker').forEach(function(el) {
            el.type = 'date';
            el.placeholder = 'تاریخ را انتخاب کنید';
        });
    }
}

if (document.readyState === 'complete') {
    initDatepickers();
} else {
    document.addEventListener('DOMContentLoaded', initDatepickers);
}
setTimeout(initDatepickers, 500);

// ====== Lightbox کیبورد ======
document.addEventListener('keydown', function(e) {
    if (!document.getElementById('lightboxOverlay').classList.contains('active')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') changeLightbox(-1);
    if (e.key === 'ArrowRight') changeLightbox(1);
});

// ====== بستن توست ======
function closeToast(toastId) {
    var toast = document.getElementById(toastId);
    if (toast) {
        toast.classList.add('hide');
        setTimeout(function() { toast.style.display = 'none'; }, 500);
    }
}

// تایمر توست‌ها
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.custom-toast').forEach(function(toast) {
        setTimeout(function() {
            toast.classList.add('hide');
            setTimeout(function() { toast.style.display = 'none'; }, 500);
        }, 5000);
    });
});

// ====== مدیریت مودال خطای رزرو ======
function closeBookingModal() {
    var modal = document.getElementById('bookingErrorModal');
    if (modal) {
        modal.style.animation = 'modalFadeOut 0.3s ease forwards';
        setTimeout(function() { modal.style.display = 'none'; }, 300);
    }
}

var styleSheet = document.createElement("style");
styleSheet.textContent = `
    @keyframes modalFadeOut {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0; transform: scale(0.9); }
    }
`;
document.head.appendChild(styleSheet);

document.addEventListener('click', function(e) {
    var modal = document.getElementById('bookingErrorModal');
    if (modal && modal.style.display !== 'none') {
        var container = modal.querySelector('.modal-container');
        if (container && !container.contains(e.target)) {
            closeBookingModal();
        }
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeBookingModal();
    }
});
</script>

<?php include '../../includes/footer.php'; ?>