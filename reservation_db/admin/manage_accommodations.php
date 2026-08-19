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

$message = '';
$error = '';

// ====== حذف اقامتگاه ======
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM accommodations WHERE id = ?");
    $stmt->execute([$id]);
    $message = '✅ اقامتگاه با موفقیت حذف شد.';
}

// ====== افزودن/ویرایش اقامتگاه ======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    $title = trim($_POST['title']);
    $city_id = (int)$_POST['city_id'];
    $price = (float)$_POST['price_per_night'];
    $max_guests = (int)$_POST['max_guests'];
    $bedrooms = (int)$_POST['bedrooms'];
    $beds = (int)$_POST['beds'];
    $bathrooms = (int)$_POST['bathrooms'];
    $type = trim($_POST['accommodation_type']);
    $description = trim($_POST['description']);
    $has_wifi = isset($_POST['has_wifi']) ? 1 : 0;
    $has_parking = isset($_POST['has_parking']) ? 1 : 0;
    $has_kitchen = isset($_POST['has_kitchen']) ? 1 : 0;
    $has_tv = isset($_POST['has_tv']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($title) || $city_id <= 0 || $price <= 0) {
        $error = '❌ لطفاً تمام فیلدهای ضروری را پر کنید.';
    } else {
        if ($edit_id > 0) {
            $stmt = $pdo->prepare("
                UPDATE accommodations SET 
                    title = ?, city_id = ?, price_per_night = ?, max_guests = ?,
                    bedrooms = ?, beds = ?, bathrooms = ?, accommodation_type = ?,
                    description = ?, has_wifi = ?, has_parking = ?, has_kitchen = ?,
                    has_tv = ?, is_active = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $city_id, $price, $max_guests, $bedrooms, $beds, $bathrooms, $type, $description, $has_wifi, $has_parking, $has_kitchen, $has_tv, $is_active, $edit_id]);
            $message = '✅ اقامتگاه با موفقیت ویرایش شد.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO accommodations 
                (owner_id, city_id, title, description, price_per_night, max_guests, bedrooms, beds, bathrooms, accommodation_type, has_wifi, has_parking, has_kitchen, has_tv, is_active) 
                VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$city_id, $title, $description, $price, $max_guests, $bedrooms, $beds, $bathrooms, $type, $has_wifi, $has_parking, $has_kitchen, $has_tv, $is_active]);
            $message = '✅ اقامتگاه جدید با موفقیت اضافه شد.';
        }
    }
}

$accommodations = $pdo->query("
    SELECT a.*, c.name as city_name 
    FROM accommodations a
    JOIN cities c ON a.city_id = c.id
    ORDER BY a.id DESC
")->fetchAll();

$cities = $pdo->query("SELECT * FROM cities ORDER BY name")->fetchAll();

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
}
.glass-card:hover {
    border-color: #ffb07c;
    box-shadow: 0 12px 30px rgba(255, 176, 124, 0.18);
}
.admin-table {
    background: rgba(255, 245, 235, 0.6);
    backdrop-filter: blur(8px);
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid rgba(255, 176, 124, 0.2);
}
.admin-table th {
    color: #b35e2a;
    font-weight: 700;
    padding: 12px 15px;
border-bottom: 2px solid rgba(255, 176, 124, 0.25);
}
.admin-table td {
    padding: 12px 15px;
    color: #4a3729;
    border-bottom: 1px solid rgba(255, 176, 124, 0.1);
}
.admin-table tr:hover td {
    background: rgba(255, 176, 124, 0.08);
}
.btn-glass {
    background: rgba(255, 176, 124, 0.2);
    border: 1px solid rgba(255, 176, 124, 0.3);
    color: #b35e2a;
    padding: 5px 14px;
    border-radius: 30px;
    font-size: 0.85rem;
    transition: 0.3s;
    text-decoration: none;
    display: inline-block;
}
.btn-glass:hover {
    background: #ffb07c;
    color: #fff;
    border-color: #ffb07c;
}
.glass-form {
    background: rgba(255, 245, 235, 0.6);
    backdrop-filter: blur(12px);
    border-radius: 24px;
    padding: 1.5rem;
    border: 1px solid rgba(255, 176, 124, 0.2);
}
.glass-form label {
    color: #4a3729;
    font-weight: 500;
}
.glass-form .form-control,
.glass-form .form-select {
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid rgba(255, 176, 124, 0.3);
    color: #4a3729;
    border-radius: 12px;
}
.glass-form .form-control:focus {
    border-color: #ffb07c;
    box-shadow: 0 0 0 3px rgba(255, 176, 124, 0.15);
}
h2, h5, .fw-bold {
    color: #b35e2a;
}
.text-muted {
    color: #8a6b55 !important;
}
</style>

<div class="container py-4">
    <h2 class="fw-bold mb-4">🏠 مدیریت اقامتگاه‌ها</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="glass-form mb-4">
        <h5 class="fw-bold">➕ افزودن اقامتگاه جدید</h5>
        <form method="POST" class="row g-3">
            <div class="col-md-4">
                <label>عنوان</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>شهر</label>
                <select name="city_id" class="form-select" required>
                    <option value="">انتخاب کنید</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?= $city['id'] ?>"><?= $city['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label>نوع اقامتگاه</label>
                <input type="text" name="accommodation_type" class="form-control" placeholder="ویلا، سوییت، کلبه، ...">
            </div>
            <div class="col-md-3">
                <label>قیمت هر شب (تومان)</label>
                <input type="number" name="price_per_night" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label>ظرفیت</label>
                <input type="number" name="max_guests" class="form-control" value="2">
            </div>
            <div class="col-md-2">
                <label>اتاق</label>
                <input type="number" name="bedrooms" class="form-control" value="1">
            </div>
            <div class="col-md-2">
                <label>تخت</label>
                <input type="number" name="beds" class="form-control" value="1">
            </div>
            <div class="col-md-2">
                <label>سرویس</label>
                <input type="number" name="bathrooms" class="form-control" value="1">
            </div>
            <div class="col-md-12">
                <label>توضیحات</label>
                <textarea name="description" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-md-12">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="has_wifi"> وای‌فای
                </div>
<div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="has_parking"> پارکینگ
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="has_kitchen"> آشپزخانه
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="has_tv"> تلویزیون
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="is_active" checked> فعال
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-warning" style="background: #ffb07c; border: none; color: #4a3729; font-weight: 600;">ذخیره اقامتگاه</button>
            </div>
        </form>
    </div>

    <div class="admin-table">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>عنوان</th>
                    <th>شهر</th>
                    <th>قیمت</th>
                    <th>ظرفیت</th>
                    <th>نوع</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($accommodations as $acc): ?>
                <tr>
                    <td><?= $acc['id'] ?></td>
                    <td><?= htmlspecialchars($acc['title']) ?></td>
                    <td><?= $acc['city_name'] ?></td>
                    <td><?= number_format($acc['price_per_night']) ?></td>
                    <td><?= $acc['max_guests'] ?></td>
                    <td><?= $acc['accommodation_type'] ?></td>
                    <td><?= $acc['is_active'] ? '🟢 فعال' : '🔴 غیرفعال' ?></td>
                    <td>
                        <a href="manage_accommodations.php?edit=<?= $acc['id'] ?>" class="btn-glass">ویرایش</a>
                        <a href="manage_accommodations.php?delete=<?= $acc['id'] ?>" class="btn-glass" style="border-color:#dc3545;color:#dc3545;" onclick="return confirm('آیا از حذف این اقامتگاه مطمئن هستید؟')">حذف</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

