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

// آمار کلی
$total_accommodations = $pdo->query("SELECT COUNT(*) FROM accommodations WHERE is_active = 1")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_reservations = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'confirmed'")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total_price) FROM reservations WHERE status = 'confirmed'")->fetchColumn();

// رزروهای امروز
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE status = 'confirmed' AND check_in <= ? AND check_out >= ?");
$stmt->execute([$today, $today]);
$today_reservations = $stmt->fetchColumn();

include '../includes/header.php';
?>

<style>
/* ====== پس‌زمینه روشن با گرادینت نارنجی ====== */
body {
    background: linear-gradient(145deg, #fff5eb, #ffe8d6, #ffdcc2);
    color: #4a3729;
    font-family: 'Vazirmatn', 'Tahoma', sans-serif;
}
.glass-card {
    background: rgba(255, 216, 177, 0.65);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
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

/* ====== کارت‌های آمار ====== */
.dashboard-grid {
    color: #ffceb5;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.stat-card {
    background: rgb(253, 212, 188);
    backdrop-filter: blur(8px);
    border-radius: 24px;
    padding: 1.2rem 0.8rem;
    text-align: center;
    border: 1px solid rgba(255, 176, 124, 0.25);
    transition: 0.3s;
    min-height: 110px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.stat-card:hover {
    transform: translateY(-4px);
    border-color: #ff8a5a;
    box-shadow: 0 12px 25px rgba(255, 176, 124, 0.15);
}
.stat-card .icon {
    font-size: 1.8rem;
    color: #e07c3e;
    margin-bottom: 0.3rem;
}
.stat-card .number {
    font-size: 1.6rem;
    font-weight: 800;
    color: #b35e2a;
    line-height: 1.2;
    word-break: break-word;  /* جلوگیری از سرریز */
}
.stat-card .label {
    color: #7a5a44;
    font-size: 0.85rem;
    margin-top: 0.2rem;
}

/* ====== دکمه‌ها ====== */
.btn-outline-orange {
    border: 2px solid #ffb07c;
    color: #b35e2a;
    background: transparent;
    border-radius: 50px;
    padding: 0.4rem 1.2rem;
    font-weight: 600;
    transition: 0.3s;
}
.btn-outline-orange:hover {
    background: #ffb07c;
    color: #fff;
    border-color: #ffb07c;
}

/* ====== لیست فعالیت‌ها ====== */
.text-muted {
    color: #8a6b55 !important;
}
.text-white-50 {
    color: #5a4a3a !important;
}
.border-light {
    border-color: rgba(255, 176, 124, 0.2) !important;
}
.fw-bold {
    color: #b35e2a;
}
h2, h5 {
    color: #b35e2a;
}
</style>

<div class="container py-4">
    <h2 class="fw-bold mb-4" style="color: #b35e2a;">📊 داشبورد مدیریت</h2>

    <div class="dashboard-grid">
        <div class="stat-card">
            <div class="icon"><i class="fas fa-home"></i></div>
            <div class="number"><?= number_format($total_accommodations) ?></div>
            <div class="label">اقامتگاه فعال</div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-users"></i></div>
            <div class="number"><?= number_format($total_users) ?></div>
            <div class="label">کاربران ثبت‌نام شده</div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-calendar-check"></i></div>
<div class="number"><?= number_format($total_reservations) ?></div>
            <div class="label">رزروهای تایید شده</div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-coin"></i></div>
            <div class="number" style="font-size:1.3rem;"><?= number_format(isset($total_revenue) ? $total_revenue : 0) ?> تومان</div>
            <div class="label">درآمد کل</div>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-calendar-day"></i></div>
            <div class="number"><?= number_format($today_reservations) ?></div>
            <div class="label">رزروهای امروز</div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="glass-card p-4">
                <h5 class="fw-bold">⚡ دسترسی سریع</h5>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a href="manage_accommodations.php" class="btn btn-outline-orange">مدیریت اقامتگاه‌ها</a>
                    <a href="manage_users.php" class="btn btn-outline-orange">مدیریت کاربران</a>
                    <a href="reports.php" class="btn btn-outline-orange">گزارش‌ها</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="glass-card p-4">
                <h5 class="fw-bold">📌 آخرین فعالیت‌ها</h5>
                <?php
                $stmt = $pdo->query("
                    SELECT r.*, u.full_name, a.title 
                    FROM reservations r
                    JOIN users u ON r.guest_id = u.id
                    JOIN accommodations a ON r.accommodation_id = a.id
                    ORDER BY r.created_at DESC LIMIT 5
                ");
                $recent = $stmt->fetchAll();
                ?>
                <?php if (count($recent) == 0): ?>
                    <p class="text-muted">هیچ فعالیتی ثبت نشده است.</p>
                <?php else: ?>
                    <ul class="list-unstyled">
                        <?php foreach ($recent as $row): ?>
                            <li class="text-muted mb-2 border-bottom border-light pb-2">
                                <i class="fas fa-user-check" style="color: #e07c3e;"></i>
                                <?= htmlspecialchars($row['full_name']) ?> 
                                <strong><?= htmlspecialchars($row['title']) ?></strong> 
                                (<?= $row['check_in'] ?> تا <?= $row['check_out'] ?>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

