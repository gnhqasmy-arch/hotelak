<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../../config/database.php';
require_once '../../config/functions.php';
$body_class ='contact-page';

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// ====== دریافت پیام‌ها از سشن ======
$message = isset($_SESSION['profile_success']) ? $_SESSION['profile_success'] : '';
$error = isset($_SESSION['profile_error']) ? $_SESSION['profile_error'] : '';
unset($_SESSION['profile_success']);
unset($_SESSION['profile_error']);

// دریافت اطلاعات کاربر
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// ====== حذف عکس پروفایل ======
if (isset($_GET['delete_photo'])) {
    if (!empty($user['profile_image']) && file_exists('../../uploads/profiles/' . $user['profile_image'])) {
        unlink('../../uploads/profiles/' . $user['profile_image']);
    }
    $stmt = $pdo->prepare("UPDATE users SET profile_image = NULL WHERE id = ?");
    $stmt->execute([$user_id]);
    $_SESSION['profile_success'] = '✅ عکس پروفایل با موفقیت حذف شد.';
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// ====== ذخیره اطلاعات ======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $national_code = isset($_POST['national_code']) ? trim($_POST['national_code']) : '';
    $birth_date = isset($_POST['birth_date']) ? trim($_POST['birth_date']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';

    // ====== اعتبارسنجی کد ملی ======
    if (!empty($national_code)) {
        // حذف فاصله‌ها و کاراکترهای اضافی
        $national_code = preg_replace('/[^0-9]/', '', $national_code);
        
        // بررسی طول (10 رقم)
        if (strlen($national_code) !== 10) {
            $_SESSION['profile_error'] = '❌ کد ملی باید دقیقاً ۱۰ رقم باشد.';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    $profile_image = isset($user['profile_image']) ? $user['profile_image'] : '';
    $error = '';
    
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_name = $_FILES['profile_image']['name'];
        $file_tmp = $_FILES['profile_image']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_size = $_FILES['profile_image']['size'];

        if (!in_array($file_ext, $allowed)) {
            $error = 'فرمت فایل مجاز نیست. فقط JPG, PNG, GIF, WEBP مجاز است.';
        } elseif ($file_size > 2 * 1024 * 1024) {
            $error = 'حجم فایل باید کمتر از ۲ مگابایت باشد.';
        } else {
            $new_name = 'user_' . $user_id . '_' . time() . '.' . $file_ext;
            $upload_path = '../../uploads/profiles/' . $new_name;
            
            if (!is_dir('../../uploads/profiles/')) {
                mkdir('../../uploads/profiles/', 0777, true);
            }
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                if (!empty($user['profile_image']) && file_exists('../../uploads/profiles/' . $user['profile_image'])) {
                    unlink('../../uploads/profiles/' . $user['profile_image']);
                }
                $profile_image = $new_name;
            } else {
                $error = 'خطا در آپلود عکس.';
            }
        }
    }

    if (empty($error)) {
        $stmt = $pdo->prepare("
            UPDATE users SET 
                national_code = ?,
                birth_date = ?,
                address = ?,
                gender = ?,
                bio = ?,
                profile_image = ?
WHERE id = ?
        ");
        if ($stmt->execute([$national_code, $birth_date, $address, $gender, $bio, $profile_image, $user_id])) {
            $_SESSION['profile_success'] = '✅ اطلاعات با موفقیت ذخیره شد.';
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } else {
            $_SESSION['profile_error'] = '❌ خطا در ذخیره اطلاعات.';
        }
    } else {
        $_SESSION['profile_error'] = $error;
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

include '../../includes/header.php';
?>

<div class="profile-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-8">
                <div class="profile-card profile-orange-glass">

                    <!-- ====== هدر پروفایل با عکس ====== -->
                    <div class="profile-header text-center">
                        <div class="profile-avatar-container">
                            <?php if (!empty($user['profile_image']) && file_exists('../../uploads/profiles/' . $user['profile_image'])): ?>
                                <img src="<?php echo BASE_URL; ?>/uploads/profiles/<?php echo $user['profile_image']; ?>" class="profile-avatar-img" alt="عکس پروفایل">
                                <!-- ====== دکمه حذف عکس (سطل اشغال) ====== -->
                                <a href="?delete_photo=1" class="avatar-delete-btn" onclick="return confirm('آیا از حذف عکس پروفایل مطمئن هستید؟')" title="حذف عکس">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            <?php else: ?>
                                <div class="profile-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                            <?php endif; ?>
                            
                            <form action="" method="POST" enctype="multipart/form-data" class="profile-avatar-upload">
                                <label for="profile_image_upload" class="avatar-upload-btn">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" id="profile_image_upload" name="profile_image" accept="image/*" style="display:none;" onchange="this.form.submit()">
                            </form>
                        </div>

                        <h2 class="profile-name"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                        <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="profile-role">
                            <span class="badge bg-primary"><?php echo $user['role'] === 'owner' ? 'مالک' : 'مسافر'; ?></span>
                        </p>
                    </div>

                    <!-- ====== فرم اطلاعات ====== -->
                    <form action="" method="POST" class="profile-form" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>کد ملی</label>
                                    <input type="text" name="national_code" class="form-control" placeholder="۱۲۳۴۵۶۷۸۹۰" maxlength="10" value="<?php echo isset($user['national_code']) ? htmlspecialchars($user['national_code']) : ''; ?>">
                                    <small class="form-text text-muted">کد ملی باید دقیقاً ۱۰ رقم باشد.</small>
                                </div>
                            </div>
                           <div class="col-md-6">
    <div class="form-group">
        <label>تاریخ تولد (قمری)</label>
        <div class="date-input-wrapper">
            <input type="text" id="birth_date" name="birth_date" class="form-control" placeholder="مثلاً ۱۴۴۵/۰۱/۰۱" value="<?php echo isset($user['birth_date']) ? htmlspecialchars($user['birth_date']) : ''; ?>" autocomplete="off">
            <span class="date-icon" id="birth_date_icon">
                <i class="fas fa-calendar-alt"></i>
            </span>
        </div>
    </div>
</div>
                        </div>

                        <div class="form-group">
                            <label>آدرس</label>
                            <textarea name="address" class="form-control" rows="3" placeholder="آدرس کامل خود را وارد کنید"><?php echo isset($user['address']) ? htmlspecialchars($user['address']) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>جنسیت</label>
                            <select name="gender" class="form-control">
                                <option value="">انتخاب کنید</option>
                                <option value="male" <?php echo (isset($user['gender']) && $user['gender'] == 'male') ? 'selected' : ''; ?>>مرد</option>
                                <option value="female" <?php echo (isset($user['gender']) && $user['gender'] == 'female') ? 'selected' : ''; ?>>زن</option>
                                <option value="other" <?php echo (isset($user['gender']) && $user['gender'] == 'other') ? 'selected' : ''; ?>>سایر</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>درباره من</label>
                            <textarea name="bio" class="form-control" rows="4" placeholder="توضیحات مختصر درباره خودتان..."><?php echo isset($user['bio']) ? htmlspecialchars($user['bio']) : ''; ?></textarea>
                        </div>
                        <div class="profile-actions">
                            <button type="submit" class="btn btn-primary btn-lg">💾 ذخیره اطلاعات</button>
                            <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-outline-secondary btn-lg">بازگشت</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- ====== کادر پیام موفقیت (در صورت وجود) ====== -->
<?php if (isset($message) && !empty($message)): ?>
<div id="successToast" class="custom-toast success-toast">
    <div class="toast-content">
        <i class="fas fa-check-circle"></i>
        <span class="toast-message"><?php echo $message; ?></span>
        <button class="toast-close" onclick="closeToast('successToast')">&times;</button>
    </div>
    <div class="toast-progress"></div>
</div>
<?php endif; ?>

<!-- ====== کادر پیام خطا (در صورت وجود) ====== -->
<?php if (isset($error) && !empty($error)): ?>
<div id="errorToast" class="custom-toast error-toast">
    <div class="toast-content">
        <i class="fas fa-times-circle"></i>
        <span class="toast-message"><?php echo $error; ?></span>
        <button class="toast-close" onclick="closeToast('errorToast')">&times;</button>
    </div>
    <div class="toast-progress"></div>
</div>
<?php endif; ?>

<!-- ====== اسکریپت تقویم قمری ====== -->
<!-- ====== اسکریپت تقویم قمری با آیکون ====== -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/persian-date@1.0.0/dist/persian-date.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.0.0/dist/js/persian-datepicker.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.0.0/dist/css/persian-datepicker.min.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
    var birthInput = document.getElementById('birth_date');
    var birthIcon = document.getElementById('birth_date_icon');
    
    if (birthInput && typeof $.fn.persianDatepicker !== 'undefined') {
        // مقداردهی اولیه تقویم
        $(birthInput).persianDatepicker({
            format: 'YYYY/MM/DD',
            autoClose: true,
            initialValue: false,
            calendar: {
                type: 'hijri'  // قمری
            },
            onSelect: function() {
                // پس از انتخاب، تقویم بسته می‌شود
            }
        });
        
        // باز کردن تقویم با کلیک روی آیکون
        if (birthIcon) {
            birthIcon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(birthInput).persianDatepicker('show');
            });
        }
        
        // باز کردن تقویم با کلیک روی فیلد
        birthInput.addEventListener('click', function() {
            $(this).persianDatepicker('show');
        });
    }
});
</script>

<script>
/**
 * نمایش و مدیریت کادر پیام با تایمر
 */

// ====== بستن دستی کادر ======
function closeToast(toastId) {
    var toast = document.getElementById(toastId);
    if (toast) {
        toast.classList.add('hide');
        setTimeout(function() {
            toast.style.display = 'none';
        }, 500);
    }
}
// ====== تایمر خودکار برای کادرهای پیام ======
document.addEventListener('DOMContentLoaded', function() {
    var successToast = document.getElementById('successToast');
    var errorToast = document.getElementById('errorToast');
    
    if (successToast) {
        setTimeout(function() {
            closeToast('successToast');
        }, 5000);
    }
    
    if (errorToast) {
        setTimeout(function() {
            closeToast('errorToast');
        }, 5000);
    }
});
</script>

<?php include '../../includes/footer.php'; ?>

