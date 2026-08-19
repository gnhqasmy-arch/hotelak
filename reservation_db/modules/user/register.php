<?php
// ====== غیرفعال کردن کش ======
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
// ====== شروع سشن (اگر قبلاً شروع نشده باشد) ======
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    
}
require_once '../../config/database.php';
require_once '../../config/functions.php';

if (isLoggedIn()) {
    redirect('../../index.php');
}

// ====== دریافت خطا از سشن (اگر وجود داشته باشد) ======
$error = isset($_SESSION['register_error']) ? $_SESSION['register_error'] : '';
$success = isset($_SESSION['register_success']) ? $_SESSION['register_success'] : '';
unset($_SESSION['register_error']);
unset($_SESSION['register_success']);

// ====== تابع اعتبارسنجی رمز عبور ======
function validatePassword($password) {
    if (strlen($password) < 8) {
        return 'رمز عبور باید حداقل ۸ کاراکتر باشد.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        return 'رمز عبور باید حداقل شامل یک عدد باشد.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return 'رمز عبور باید حداقل شامل یک حرف بزرگ (A-Z) باشد.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        return 'رمز عبور باید حداقل شامل یک حرف کوچک (a-z) باشد.';
    }
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        return 'رمز عبور باید حداقل شامل یک کاراکتر خاص (مثل @, #, $, %, ^, &, *, !) باشد.';
    }
    $weakPasswords = ['12345678', 'password', '123456789', 'qwerty', 'abc123', 'admin123', 'welcome'];
    if (in_array(strtolower($password), $weakPasswords)) {
        return 'رمز عبور انتخابی بسیار ضعیف است. لطفاً یک رمز قوی‌تر انتخاب کنید.';
    }
    return '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm)) {
        $_SESSION['register_error'] = 'لطفاً تمام فیلدهای ضروری را پر کنید.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = 'لطفاً یک ایمیل معتبر وارد کنید.';
    } elseif ($password !== $confirm) {
        $_SESSION['register_error'] = 'رمز عبور و تکرار آن مطابقت ندارند.';
    } else {
        $passwordError = validatePassword($password);
        if (!empty($passwordError)) {
            $_SESSION['register_error'] = $passwordError;
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
    $_SESSION['register_error'] = 'این ایمیل قبلاً ثبت شده است.';
    redirect('../../index.php'); // ✅ به صفحه اصلی می‌رود و توست نمایش داده می‌شود
}else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, 'guest')");
                if ($stmt->execute([$full_name, $email, $phone, $hashed_password])) {
                    $_SESSION['register_success'] = 'ثبت‌نام با موفقیت انجام شد. اکنون می‌توانید وارد شوید.';
                    redirect('login.php');
                } else {
                    $_SESSION['register_error'] = 'خطا در ثبت‌نام. لطفاً دوباره تلاش کنید.';
                }
            }
        }
    }
    // ====== در register.php، جایی که خطا را تشخیص می‌دهید ======
if ($password !== $confirm) {
    $_SESSION['register_error'] = 'رمز عبور و تکرار آن مطابقت ندارند.';
    redirect('../../index.php');
}

if (!empty($passwordError)) {
    $_SESSION['register_error'] = $passwordError; // مثلاً 'رمز عبور باید شامل حرف بزرگ باشد'
    redirect('../../index.php');
}
    // در صورت وجود خطا، به همان صفحه برگرد
    redirect('../../index.php');
}

include '../../includes/header.php';
?>

<!-- ====== استایل‌های توست خطا ====== -->
<style>
    .toast-error {
        background: #f8d7da !important;
        border-color: #dc3545 !important;
    }
    .toast-error .toast-content i {
        color: #dc3545 !important;
    }
    .toast-error .toast-progress {
        background: #dc3545 !important;
    }
    .toast-error .toast-message {
        color: #721c24 !important;
    }
</style>

<div class="auth-page">
    <div class="container">
<div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="auth-card glass-card">
                    <h2 class="auth-title">📝 ثبت‌نام</h2>
                    <p class="auth-desc">برای استفاده از خدمات، ثبت‌نام کنید.</p>

                    <!-- ====== نمایش توست خطا (در صورت وجود) ====== -->
                   <?php if (!empty($error)): ?>
<div id="registerErrorToast" class="custom-toast toast-error">
    <div class="toast-content">
        <span class="toast-icon"><i class="fas fa-exclamation-circle"></i></span>
        <span class="toast-message"><?= htmlspecialchars($error) ?></span>
        <button class="toast-close" onclick="closeToast('registerErrorToast')">&times;</button>
    </div>
    <div class="toast-progress"><span class="progress-bar"></span></div>
</div>
<?php endif; ?>

                    <!-- ====== پیام موفقیت (در صورت وجود) ====== -->
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <form action="" method="POST" class="auth-form">
                        <div class="form-group">
                            <label>نام و نام خانوادگی</label>
                            <input type="text" name="full_name" class="form-control" placeholder="نام خود را وارد کنید" required>
                        </div>
                        <div class="form-group">
                            <label>ایمیل</label>
                            <input type="email" name="email" class="form-control" placeholder="ایمیل خود را وارد کنید" required>
                        </div>
                        <div class="form-group">
                            <label>شماره تلفن (اختیاری)</label>
                            <input type="text" name="phone" class="form-control" placeholder="۰۹۱۲۳۴۵۶۷۸۹">
                        </div>
                        <div class="form-group password-field">
    <label>رمز عبور</label>
    <div class="password-wrapper">
        <input type="password" name="password" id="registerPassword" class="form-control" placeholder="حداقل ۸ کاراکتر با حروف بزرگ/کوچک/عدد/علامت" required>
        <span class="password-toggle" onclick="togglePasswordVisibility('registerPassword', this)">
            <i class="fas fa-eye"></i>
        </span>
    </div>
    <small class="text-muted" style="display:block; margin-top:5px;">
        رمز باید حداقل ۸ کاراکتر، شامل حرف بزرگ، حرف کوچک، عدد و کاراکتر خاص باشد.
    </small>
</div>

<div class="form-group password-field">
    <label>تکرار رمز عبور</label>
    <div class="password-wrapper">
        <input type="password" name="confirm_password" id="registerConfirmPassword" class="form-control" placeholder="رمز عبور را دوباره وارد کنید" required>
        <span class="password-toggle" onclick="togglePasswordVisibility('registerConfirmPassword', this)">
            <i class="fas fa-eye"></i>
        </span>
    </div>
</div>
                        <button type="submit" class="btn btn-primary auth-btn">ثبت‌نام</button>
                    </form>

                    <p class="auth-link">
                        قبلاً حساب دارید؟ <a href="<?= BASE_URL ?>/modules/user/login.php">وارد شوید</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
// ====== بستن توست با دکمه ======
function closeToast(toastId) {
    var toast = document.getElementById(toastId);
    if (toast) {
        toast.classList.add('hide');
        setTimeout(function() {
            toast.style.display = 'none';
        }, 500);
    }
}

// ====== تایمر خودکار برای همه توست‌ها (۶ ثانیه) ======
document.addEventListener('DOMContentLoaded', function() {
    var toasts = document.querySelectorAll('.custom-toast:not(.hide)');
    toasts.forEach(function(toast) {
        // بعد از ۶ ثانیه، توست را مخفی کن
        setTimeout(function() {
            closeToast(toast.id);
        }, 6000);
    });
});
</script>

<?php include '../../includes/footer.php'; ?>

