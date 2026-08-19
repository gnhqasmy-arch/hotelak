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

if (isset($_GET['role']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $role = $_GET['role'];
    if (in_array($role, ['guest', 'owner', 'admin'])) {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $id]);
        $message = '✅ نقش کاربر با موفقیت تغییر کرد.';
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    $stmt->execute([$id]);
    $message = '✅ کاربر با موفقیت حذف شد.';
}

$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
include '../includes/header.php';
?>

<style>
body {
    background: linear-gradient(145deg, #fff5eb, #ffe8d6, #ffdcc2);
    color: #4a3729;
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
h2 {
    color: #b35e2a;
}
</style>

<div class="container py-4">
    <h2 class="fw-bold mb-4">👥 مدیریت کاربران</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <div class="admin-table">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>نام</th>
                    <th>ایمیل</th>
                    <th>شماره</th>
                    <th>نقش</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                    <td><?= $user['email'] ?></td>
                    <td><?= isset($user['phone']) ? $user['phone'] : '-' ?></td>
                    <td>
                        <span class="badge bg-<?= $user['role'] == 'admin' ? 'danger' : ($user['role'] == 'owner' ? 'warning' : 'secondary') ?>">
                            <?= $user['role'] ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($user['role'] != 'admin'): ?>
                            <a href="manage_users.php?role=owner&id=<?= $user['id'] ?>" class="btn-glass">مالک</a>
                            <a href="manage_users.php?role=guest&id=<?= $user['id'] ?>" class="btn-glass">مسافر</a>
                            <a href="manage_users.php?delete=<?= $user['id'] ?>" class="btn-glass" style="border-color:#dc3545;color:#dc3545;" onclick="return confirm('حذف کاربر؟')">حذف</a>
                        <?php else: ?>
                            <span class="text-muted">ادمین</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>