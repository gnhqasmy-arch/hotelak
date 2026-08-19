<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
$hide_search =true;
require_once 'config/database.php';
require_once 'config/functions.php';
$body_class ='contact-page';
include 'includes/header.php';

// پیام موفقیت/خطا
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = 'لطفاً تمام فیلدها را پر کنید.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'لطفاً یک ایمیل معتبر وارد کنید.';
    } else {
        $success_message = 'پیام شما با موفقیت ارسال شد. به زودی با شما تماس خواهیم گرفت.';
    }
}

?>

<div class="contact-page-new">

    <!-- ====== بخش اصلی با پس‌زمینه ====== -->
    <section class="contact-hero-new" style="background-image: url('<?= BASE_URL ?>/uploads/3d-rendering.jpg');">
        <div class="contact-hero-overlay-new"></div>
        <div class="container contact-hero-container">
            <div class="contact-grid-new">
                   

                <!-- ====== عکس سمت راست ====== -->
                <div class="contact-image-wrapper animate-on-scroll">
                    <div class="contact-image-box">
                        <img src="<?= BASE_URL ?>/uploads/original-cd6d00.gif" alt="تماس با ما" class="contact-side-image" onerror="this.src='https://via.placeholder.com/600x400?text=Contact+Us'">
                        <div class="contact-image-badge">
                            <i class="fas fa-headset"></i>
                            <span>پشتیبانی ۲۴ ساعته</span>
                        </div>
                    </div>
                </div>

                <!-- ====== فرم تماس داخل کادر ====== -->
                <div class="contact-form-wrapper animate-on-scroll" style="animation-delay: 0.2s;">
                    <div class="contact-card">
                        <h2 class="contact-form-title">ارسال پیام</h2>
                        <p class="contact-form-desc">ما مشتاق شنیدن صدای شما هستیم</p>

                        <?php if ($success_message): ?>
                            <div class="contact-alert success">✅ <?= htmlspecialchars($success_message) ?></div>
                        <?php endif; ?>
                        <?php if ($error_message): ?>
                            <div class="contact-alert error">❌ <?= htmlspecialchars($error_message) ?></div>
                        <?php endif; ?>

                        <form action="" method="POST" class="contact-form-new">
                            <div class="form-group-new">
                                <input type="text" id="name" name="name" class="form-control-new" placeholder="نام و نام خانوادگی" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
                                <i class="fas fa-user form-icon"></i>
                            </div>
                            <div class="form-group-new">
                                <input  type="email" id="email" name="email" class="form-control-new" placeholder="ایمیل" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                                <i class="fas fa-envelope form-icon"></i>
                            </div>
                            <div class="form-group-new">
                                <input type="text" id="subject" name="subject" class="form-control-new" placeholder="موضوع" value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '' ?>" required>
                                <i class="fas fa-tag form-icon"></i>
</div>
                            <div class="form-group-new">
                                <textarea id="message" name="message" class="form-control-new" rows="4" placeholder="پیام خود را بنویسید..." required><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
                                <i class="fas fa-pencil-alt form-icon"></i>
                            </div>
                            <button type="submit" class="btn btn-primary btn-submit-new">
                                ارسال پیام <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>

                        <!-- اطلاعات تماس داخل کارت -->
                        <div class="contact-info-inline">
                            <div class="info-item">
                                <i class="fas fa-phone-alt"></i>
                                <span><a href="tel:02112345678">۰۲۱-۱۲۳۴۵۶۷۸</a></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-envelope"></i>
                                <span><a href="mailto:info@suitereserve.com">info@suitereserve.com</a></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>تهران، ولیعصر، پلاک ۱۲۳</span>

                            </div>
                        </div>
                    </div>
                
                        </div>
 <!-- ====== بخش شبکه‌های اجتماعی ====== -->
    <section class="contact-social-new">
        <div class="container">
            <div class="social-grid">
                <a href="#" class="social-card instagram">
                    <i class="fab fa-instagram"></i>
                    <span>اینستاگرام</span>
                </a>
                <a href="#" class="social-card telegram">
                    <i class="fab fa-telegram"></i>
                    <span>تلگرام</span>
                </a>
                <a href="#" class="social-card whatsapp">
                    <i class="fab fa-whatsapp"></i>
                    <span>واتساپ</span>
                </a>
                <a href="#" class="social-card youtube">
                    <i class="fab fa-youtube"></i>
                    <span>یوتیوب</span>
                </a>
            </div>
        </div>
    </section>

</div>
            </div>
        </div>
    </section>



<?php include 'includes/footer.php'; ?>