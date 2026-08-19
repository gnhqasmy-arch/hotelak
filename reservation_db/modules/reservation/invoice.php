<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../../config/database.php';
require_once '../../config/functions.php';

if (!isLoggedIn()) {
    redirect('../user/login.php');
}

$reservation_id = isset($_GET['reservation_id']) ? (int)$_GET['reservation_id'] : 0;
if (!$reservation_id) {
    redirect('../../index.php');
}

$stmt = $pdo->prepare("
    SELECT r.*, a.title, a.price_per_night, p.transaction_id, p.paid_at 
    FROM reservations r 
    JOIN accommodations a ON r.accommodation_id = a.id
    JOIN payments p ON r.id = p.reservation_id
    WHERE r.id = ? AND r.guest_id = ?
");
$stmt->execute([$reservation_id, $_SESSION['user_id']]);
$invoice = $stmt->fetch();

if (!$invoice) {
    redirect('../../index.php');
}

// ====== تبدیل تاریخ میلادی به شمسی ======
$paid_at_persian = '';
if (!empty($invoice['paid_at'])) {
    $timestamp = strtotime($invoice['paid_at']);
    $year = date('Y', $timestamp);
    $month = date('m', $timestamp);
    $day = date('d', $timestamp);
    // استفاده از تابع جدید
    $persian = gregorianToJalali($year, $month, $day);
    $paid_at_persian = sprintf('%04d/%02d/%02d', $persian[0], $persian[1], $persian[2]);
}

include '../../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-success text-white text-center py-3">
                    <h3 class="mb-0">✅ رزرو شما با موفقیت انجام شد</h3>
                </div>
                <div class="card-body p-4">
                    <h5 class="fw-bold">🧾 جزئیات رزرو</h5>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>اقامتگاه:</strong></td>
                            <td><?= htmlspecialchars($invoice['title']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>تاریخ ورود:</strong></td>
                            <td><?= $invoice['check_in'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>تاریخ خروج:</strong></td>
                            <td><?= $invoice['check_out'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>تعداد شب‌ها:</strong></td>
                            <td><?= nightsBetween($invoice['check_in'], $invoice['check_out']) ?> شب</td>
                        </tr>
                        <tr>
                            <td><strong>مبلغ کل:</strong></td>
                            <td><?= number_format($invoice['total_price']) ?> تومان</td>
                        </tr>
                        <tr>
                            <td><strong>کد پیگیری:</strong></td>
                            <td><code><?= htmlspecialchars($invoice['transaction_id']) ?></code></td>
                        </tr>
                        <tr>
                            <td><strong>تاریخ پرداخت:</strong></td>
                            <td><?= $paid_at_persian ?></td>
                        </tr>
                    </table>
                    
                    <div class="text-center mt-3">
                        <a href="../../index.php" class="btn btn-primary">بازگشت به صفحه اصلی</a>
                        <button onclick="window.print()" class="btn btn-outline-secondary">🖨️ چاپ فاکتور</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>