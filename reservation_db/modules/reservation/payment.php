<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../../config/database.php';
require_once '../../config/functions.php';

// ====== اگر کاربر مستقیماً به صفحه پرداخت نیامده (reservation_id در GET وجود ندارد) ======
if (!isset($_GET['reservation_id']) || empty($_GET['reservation_id'])) {
    // سشن تخفیف را پاک کن تا در صفحات دیگر نمایش داده نشود
    unset($_SESSION['discount_applied']);
    // هدایت به صفحه اصلی یا صفحه مناسب
    redirect('../../index.php');
}

if (!isLoggedIn()) {
    redirect('../user/login.php');
}



$reservation_id = isset($_GET['reservation_id']) ? (int)$_GET['reservation_id'] : 0;
if (!$reservation_id) {
    $_SESSION['payment_error'] = 'شناسه رزرو وجود ندارد.';
    redirect('../../index.php');
}

// ====== دریافت اطلاعات رزرو ======
$stmt = $pdo->prepare("
    SELECT r.*, p.id as payment_id, p.amount as payment_amount 
    FROM reservations r 
    JOIN payments p ON r.id = p.reservation_id 
    WHERE r.id = ? AND r.guest_id = ?
");
$stmt->execute([$reservation_id, $_SESSION['user_id']]);
$reservation = $stmt->fetch();

if (!$reservation) {
    $_SESSION['payment_error'] = 'رزرو یافت نشد.';
    redirect('../../index.php');
}

$original_price = $reservation['total_price'];
$final_price = $original_price;
$discount_amount = 0;
$discount_code = '';
$discount_id = null;

// ====== اعمال کد تخفیف ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_discount'])) {
    $discount_code = isset($_POST['discount_code']) ? trim($_POST['discount_code']) : '';
    
    if (!empty($discount_code)) {
        // ====== بررسی کد تخفیف ======
        $stmt = $pdo->prepare("
            SELECT * FROM discounts 
            WHERE code = ? 
            AND is_active = 1 
            AND start_date <= ? 
            AND end_date >= ?
            AND (usage_limit IS NULL OR used_count < usage_limit)
        ");
        $stmt->execute([$discount_code, date('Y-m-d'), date('Y-m-d')]);
        $discount = $stmt->fetch();
        
        if ($discount) {
            // ====== محاسبه تخفیف ======
            if ($discount['discount_type'] === 'percent') {
                $discount_amount = ($original_price * $discount['discount_value']) / 100;
                if (!empty($discount['max_discount_amount']) && $discount_amount > $discount['max_discount_amount']) {
                    $discount_amount = $discount['max_discount_amount'];
                }
            } else {
                $discount_amount = $discount['discount_value'];
                if ($discount_amount > $original_price) {
                    $discount_amount = $original_price;
                }
            }
            
            $final_price = $original_price - $discount_amount;
            $discount_id = $discount['id'];
            
            $_SESSION['discount_applied'] = [
                'code' => $discount['code'],
                'amount' => $discount_amount,
                'final_price' => $final_price,
                'discount_id' => $discount_id
            ];
        } else {
            $_SESSION['payment_error'] = 'کد تخفیف نامعتبر یا منقضی شده است.';
            unset($_SESSION['discount_applied']);
        }
    }
    header("Location: payment.php?reservation_id=$reservation_id");
    exit;
}

// ====== اگر تخفیف در سشن است ولی تاریخ آن منقضی شده، آن را پاک کن ======
if (isset($_SESSION['discount_applied']) && $_SESSION['discount_applied']['discount_id']) {
    $discount_id = $_SESSION['discount_applied']['discount_id'];
    $stmt = $pdo->prepare("
        SELECT * FROM discounts WHERE id = ? AND is_active = 1 
        AND start_date <= ? AND end_date >= ?
        AND (usage_limit IS NULL OR used_count < usage_limit)
    ");
    $stmt->execute([$discount_id, date('Y-m-d'), date('Y-m-d')]);
    $discount = $stmt->fetch();
    
    if (!$discount) {
        // اگر تخفیف منقضی شده، سشن را پاک کن
        unset($_SESSION['discount_applied']);
    } else {
        // اگر تخفیف معتبر است، آن را اعمال کن
        $discount_amount = $_SESSION['discount_applied']['amount'];
        $final_price = $original_price - $discount_amount;
        $_SESSION['discount_applied']['final_price'] = $final_price;
    }
}

// اگر تخفیف قبلاً اعمال شده، از سشن بخوان
// ====== اگر تخفیف قبلاً در سشن ذخیره شده ======
if (isset($_SESSION['discount_applied']) && $_SESSION['discount_applied']['discount_id']) {
    $discount_id = $_SESSION['discount_applied']['discount_id'];
    
    // دریافت مجدد اطلاعات تخفیف از دیتابیس
    $stmt = $pdo->prepare("SELECT * FROM discounts WHERE id = ? AND is_active = 1");
    $stmt->execute([$discount_id]);
    $discount = $stmt->fetch();
    
    if ($discount) {
        $discount_code = $discount['code'];
        
        // محاسبه مجدد مبلغ تخفیف بر اساس قیمت اصلی فعلی
        if ($discount['discount_type'] === 'percent') {
            $discount_amount = ($original_price * $discount['discount_value']) / 100;
            if (!empty($discount['max_discount_amount']) && $discount_amount > $discount['max_discount_amount']) {
                $discount_amount = $discount['max_discount_amount'];
            }
        } else { // fixed_amount
            $discount_amount = $discount['discount_value'];
            if ($discount_amount > $original_price) {
                $discount_amount = $original_price;
            }
        }
        
        $final_price = $original_price - $discount_amount;
        
        // به‌روزرسانی سشن با مقادیر جدید
        $_SESSION['discount_applied']['amount'] = $discount_amount;
        $_SESSION['discount_applied']['final_price'] = $final_price;
    } else {
        // اگر تخفیف نامعتبر شده، سشن را پاک کن
        unset($_SESSION['discount_applied']);
    }
}

include '../../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-5">

                    <h2 class="text-center mb-4">💳 پرداخت رزرو</h2>

                    <?php if (isset($_SESSION['payment_error'])): ?>
                        <div class="alert alert-danger text-center">
                            <i class="fas fa-times-circle"></i> <?= $_SESSION['payment_error'] ?>
                            <?php unset($_SESSION['payment_error']); ?>
                        </div>
                    <?php endif; ?>
                    <div class="row mb-4">
                        <div class="col-6">
                            <p class="text-muted">شماره رزرو:</p>
                            <strong>#<?= $reservation_id ?></strong>
                        </div>
                        <div class="col-6 text-end">
                            <p class="text-muted">وضعیت:</p>
                            <span class="badge bg-warning">در انتظار پرداخت</span>
                        </div>
                    </div>

                    <hr>

                    <!-- ====== قیمت ====== -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <span class="text-muted">قیمت اصلی:</span>
                        </div>
                        <div class="col-6 text-end">
                            <span id="originalPrice"><?= number_format($original_price) ?> تومان</span>
                        </div>
                    </div>

                    <?php if ($discount_amount > 0): ?>
                    <div class="row mb-3 text-success">
                        <div class="col-6">
                            <span>تخفیف (<?= $discount_code ?>):</span>
                        </div>
                        <div class="col-6 text-end">
                            <span id="discountAmount">-<?= number_format($discount_amount) ?> تومان</span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-6">
                            <strong>مبلغ قابل پرداخت:</strong>
                        </div>
                        <div class="col-6 text-end">
                            <strong class="text-success" id="finalPrice"><?= number_format($final_price) ?> تومان</strong>
                        </div>
                    </div>

                    <hr>

                    <!-- ====== کد تخفیف ====== -->
                    <div class="mb-4">
                        <label class="fw-bold">🎟️ کد تخفیف</label>
                        <div class="input-group">
                            <input type="text" id="discountInput" class="form-control" placeholder="کد تخفیف را وارد کنید" value="<?= $discount_code ?>">
                            <button class="btn btn-outline-primary" id="applyDiscountBtn">اعمال</button>
                        </div>
                        <small class="text-muted">مثلاً کد تخفیف <strong>SUMMER1404</strong> برای دریافت تخفیف ویژه.</small>
                    </div>

                    <hr>

                    <!-- ====== پرداخت ====== -->
                    <form action="payment_process.php" method="POST" class="mt-3">
                        <input type="hidden" name="reservation_id" value="<?= $reservation_id ?>">
                        <?php if ($discount_id): ?>
                            <input type="hidden" name="discount_id" value="<?= $discount_id ?>">
                        <?php endif; ?>
                        <input type="hidden" name="final_price" value="<?= $final_price ?>">
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-check-circle"></i> پرداخت 
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="../../index.php" class="btn btn-outline-secondary">بازگشت به صفحه اصلی</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('applyDiscountBtn').addEventListener('click', function() {
    const code = document.getElementById('discountInput').value.trim();
    if (!code) {alert('لطفاً کد تخفیف را وارد کنید.');
        return;
    }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '';
    const input1 = document.createElement('input');
    input1.type = 'hidden';
    input1.name = 'apply_discount';
    input1.value = '1';
    form.appendChild(input1);
    const input2 = document.createElement('input');
    input2.type = 'hidden';
    input2.name = 'discount_code';
    input2.value = code;
    form.appendChild(input2);
    document.body.appendChild(form);
    form.submit();
});
document.getElementById('discountInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('applyDiscountBtn').click();
    }
});
</script>

<?php include '../../includes/footer.php'; ?>