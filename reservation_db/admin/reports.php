<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../config/database.php';
require_once '../config/functions.php';

$hide_search = true;

if (!isLoggedIn() || $_SESSION['user_role'] != 'admin') {
    redirect('../index.php');
}

// ====== گزارش درآمد ماهانه ======
$monthly_revenue = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_price) as total
    FROM reservations WHERE status = 'confirmed'
    GROUP BY month ORDER BY month DESC LIMIT 6
")->fetchAll();

// ====== پرفروش‌ترین اقامتگاه‌ها ======
$top_accommodations = $pdo->query("
    SELECT a.title, COUNT(r.id) as reservations, SUM(r.total_price) as revenue
    FROM reservations r
    JOIN accommodations a ON r.accommodation_id = a.id
    WHERE r.status = 'confirmed'
    GROUP BY a.id
    ORDER BY revenue DESC LIMIT 5
")->fetchAll();

// ====== تعداد رزرو بر اساس وضعیت ======
$status_counts = $pdo->query("
    SELECT status, COUNT(*) as count FROM reservations GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

include '../includes/header.php';
?>

<style>
/* ====== تم روشن نارنجی ====== */
body {
    background: linear-gradient(145deg, #fff5eb, #ffe8d6, #ffdcc2);
    color: #4a3729;
}
.glass-card {
    background: rgba(255, 245, 235, 0.65);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 176, 124, 0.35);
    border-radius: 28px;
    padding: 1.8rem 2rem;
    box-shadow: 0 8px 25px rgba(255, 176, 124, 0.12);
    transition: 0.3s;
}
.glass-card:hover {
    border-color: #ffb07c;
    box-shadow: 0 12px 30px rgba(255, 176, 124, 0.18);
}
h2, h5, .fw-bold {
    color: #b35e2a;
}
.text-muted {
    color: #8a6b55 !important;
}
.list-unstyled li {
    color: #4a3729 !important;
    border-bottom: 1px solid rgba(255, 176, 124, 0.15);
    padding: 8px 0;
}
</style>

<div class="container py-4">
    <h2 class="fw-bold mb-4">📈 گزارش‌ها و آمار</h2>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="glass-card p-4">
                <h5 class="fw-bold">💰 درآمد ماهانه</h5>
                <?php if (count($monthly_revenue) == 0): ?>
                    <p class="text-muted">هیچ داده‌ای موجود نیست.</p>
                <?php else: ?>
                    <ul class="list-unstyled">
                        <?php foreach ($monthly_revenue as $row): ?>
                            <li class="d-flex justify-content-between">
                                <span><?= htmlspecialchars($row['month']) ?></span>
                                <span><?= number_format($row['total']) ?> تومان</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="glass-card p-4">
                <h5 class="fw-bold">🏆 پرفروش‌ترین اقامتگاه‌ها</h5>
                <?php if (count($top_accommodations) == 0): ?>
                    <p class="text-muted">هیچ داده‌ای موجود نیست.</p>
                <?php else: ?>
                    <ul class="list-unstyled">
                        <?php foreach ($top_accommodations as $row): ?>
                            <li class="d-flex justify-content-between">
                                <span><?= htmlspecialchars($row['title']) ?></span>
                                <span><?= number_format(isset($row['revenue']) ? $row['revenue'] : 0) ?> تومان (<?= $row['reservations'] ?> رزرو)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="glass-card p-4">
                <h5 class="fw-bold">📊 وضعیت رزروها</h5>
                <?php
                $status_labels = [
                    'pending' => 'در انتظار',
'confirmed' => 'تایید شده',
                    'cancelled' => 'لغو شده',
                    'completed' => 'تکمیل شده'
                ];
                ?>
                <ul class="list-unstyled">
                    <?php foreach ($status_labels as $key => $label): ?>
                        <li class="d-flex justify-content-between">
                            <span><?= $label ?></span>
                            <span><?= isset($status_counts[$key]) ? $status_counts[$key] : 0 ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="col-md-6">
            <div class="glass-card p-4">
                <h5 class="fw-bold">📌 رزروهای اخیر</h5>
                <?php
                $recent = $pdo->query("
                    SELECT r.*, u.full_name, a.title 
                    FROM reservations r
                    JOIN users u ON r.guest_id = u.id
                    JOIN accommodations a ON r.accommodation_id = a.id
                    ORDER BY r.created_at DESC LIMIT 5
                ")->fetchAll();
                ?>
                <?php if (count($recent) == 0): ?>
                    <p class="text-muted">هیچ رزروی ثبت نشده است.</p>
                <?php else: ?>
                    <ul class="list-unstyled">
                        <?php foreach ($recent as $row): ?>
                            <li>
                                <strong><?= htmlspecialchars($row['full_name']) ?></strong>
                                در <strong><?= htmlspecialchars($row['title']) ?></strong>
                                (<?= $row['check_in'] ?> - <?= $row['check_out'] ?>)
                                <span class="badge bg-<?= $row['status'] == 'confirmed' ? 'success' : 'secondary' ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

