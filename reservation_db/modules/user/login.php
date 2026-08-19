<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../../config/database.php';
require_once '../../config/functions.php';

// اگر کاربر وارد شده، به صفحه اصلی برو
if (isLoggedIn()) {
    redirect('../../index.php');
}

$error = '';

// پردازش فرم ورود
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '../../index.php';

    if (empty($email) || empty($password)) {
        $error = 'لطفاً ایمیل و رمز عبور را وارد کنید.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // اگر از صفحه رزرو آمده، به همان صفحه برگرد
            if (isset($_SESSION['login_redirect'])) {
                $redirect = $_SESSION['login_redirect'];
                unset($_SESSION['login_redirect']);
            }
            redirect($redirect);
        } else {
            $error = 'ایمیل یا رمز عبور اشتباه است.';
        }
    }
}

include '../../includes/header.php';
?>

<div class="auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="auth-card glass-card">
                    <h2 class="auth-title">🔐 ورود به حساب</h2>
                    <p class="auth-desc">برای ادامه، وارد حساب کاربری خود شوید.</p>

                    <?php if (isset($_SESSION['login_message'])): ?>
    <div class="alert alert-warning text-center">
        <?= $_SESSION['login_message'] ?>
    </div>
    <?php unset($_SESSION['login_message']); ?>
<?php endif; ?>
                   

                    <!-- ====== فرم ورود ====== -->
                    <form action="" method="POST" class="auth-form">
                        <?php if (isset($_GET['redirect'])): ?>
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect']) ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>ایمیل</label>
                            <input type="email" name="email" class="form-control" placeholder="ایمیل خود را وارد کنید" required>
                        </div>
                        <div class="form-group">
                            <label>رمز عبور</label>
                            <input type="password" name="password" class="form-control" placeholder="رمز عبور خود را وارد کنید" required>
                        </div>
                        <button type="submit" class="btn btn-primary auth-btn w-100">ورود</button>
                    </form>

                    <p class="auth-link mt-3">
                        حساب ندارید؟ <a href="<?= BASE_URL ?>/modules/user/register.php">ثبت‌نام کنید</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>