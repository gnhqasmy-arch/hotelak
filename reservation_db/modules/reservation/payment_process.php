<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

if (!isLoggedIn()) {
    redirect('../user/login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../../index.php');
}

$reservation_id = isset($_POST['reservation_id']) ? (int)$_POST['reservation_id'] : 0;
$final_price = isset($_POST['final_price']) ? (float)$_POST['final_price'] : 0;
$discount_id = isset($_POST['discount_id']) ? (int)$_POST['discount_id'] : null;

if (!$reservation_id) {
    $_SESSION['payment_error'] = 'شناسه رزرو نامعتبر است.';
    redirect("payment.php?reservation_id=$reservation_id");
}

$pdo->beginTransaction();

try {
    // ====== تولید کد پیگیری ======
    $transaction_id = 'TRX-' . strtoupper(uniqid()) . '-' . rand(1000, 9999);
    
    // به‌روزرسانی payments
    $stmt = $pdo->prepare("
        UPDATE payments 
        SET status = 'paid', 
            paid_at = NOW(), 
            amount = ?,
            transaction_id = ?
        WHERE reservation_id = ?
    ");
    $stmt->execute([$final_price, $transaction_id, $reservation_id]);

    // به‌روزرسانی reservations
    $stmt = $pdo->prepare("UPDATE reservations SET status = 'confirmed' WHERE id = ?");
    $stmt->execute([$reservation_id]);

    // اگر تخفیف استفاده شده، ثبت استفاده
    if ($discount_id) {
        $stmt = $pdo->prepare("INSERT INTO user_discount_usage (discount_id, user_id, reservation_id) VALUES (?, ?, ?)");
        $stmt->execute([$discount_id, $_SESSION['user_id'], $reservation_id]);

        $stmt = $pdo->prepare("UPDATE discounts SET used_count = used_count + 1 WHERE id = ?");
        $stmt->execute([$discount_id]);
    }

    $pdo->commit();
    
    $_SESSION['payment_success'] = "پرداخت با موفقیت انجام شد. کد پیگیری: $transaction_id";
    redirect("invoice.php?reservation_id=$reservation_id");

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['payment_error'] = 'خطا در پردازش پرداخت: ' . $e->getMessage();
    redirect("payment.php?reservation_id=$reservation_id");
}
?>