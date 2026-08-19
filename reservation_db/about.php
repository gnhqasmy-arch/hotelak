<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once 'config/database.php';
require_once 'config/functions.php';
$body_class ='contact-page';
include 'includes/header.php';
?>

<div class="about-page">

   <!-- ====== هدر با عکس پس‌زمینه (تمام‌عرض) ====== -->
<section class="about-hero">
    <div class="about-hero-overlay">
        <div class="about-hero-content">
            <h1 class="about-hero-title">درباره ما</h1>
            <p class="about-hero-subtitle">آشنایی با <strong>سوییت‌رزرو</strong> و تیم حرفه‌ای ما</p>
            <div class="about-hero-line"></div>
            <p class="about-hero-desc">
                ما اینجا هستیم تا بهترین تجربه اقامت در شمال ایران را برای شما رقم بزنیم.
            </p>
        </div>
    </div>
</section>
    <!-- ====== بخش معرفی شرکت ====== -->
    <section class="about-intro">
        <div class="container">
            <div class="about-intro-grid">
                <div class="about-intro-text">
                    <h2>چیزی که ما را <span class="highlight">متفاوت</span> می‌کند</h2>
                    <p>
                        <strong>هتلک</strong> با پشتوانه سال‌ها تجربه در صنعت گردشگری و مهمان‌نوازی، 
                        در سال ۱۴۰۰ فعالیت خود را آغاز کرد. هدف ما ایجاد تجربه‌ای آسان، سریع و مطمئن 
                        برای مسافران شمال ایران است.
                    </p>
                    <p>
                        ما با بهره‌گیری از تیمی مجرب و متعهد، تمام اقامتگاه‌های ثبت‌شده را بررسی کرده 
                        و با مالکان معتبر همکاری می‌کنیم تا بهترین تجربه اقامت را برای شما فراهم کنیم.
                    </p>
                    <p>
                        از روز اول، تمرکز ما بر <strong>کیفیت خدمات</strong>، <strong>پشتیبانی ۲۴ ساعته</strong> 
                        و <strong>ضمانت بهترین قیمت</strong> بوده است.
                    </p>
                    <a href="<?= BASE_URL ?>/modules/quiz/start.php" class="btn btn-primary"> دستیار سفر را امتحان کنید</a>
                </div>
                <div class="about-intro-image">
                    <img src="<?= BASE_URL ?>/uploads/about-us.jpg" alt="درباره سوییت‌رزرو">
                </div>
            </div>
        </div>
    </section>

    <!-- ====== بخش برند (جایگاه ویژه برای برند شما) ====== -->
    <section class="about-brand">
        <div class="container">
            <div class="brand-box">
                <div class="brand-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="brand-content">
                    <h3>برند <span class="brand-name">چرا هتلک</span></h3>
                    <p>
                        برند <strong>هتلک</strong> نماد اعتماد، کیفیت و تجربه‌ای به‌یادماندنی از اقامت 
                        در شمال ایران است. ما با افتخار میزبان شما در زیباترین سوئیت‌ها، ویلاها و کلبه‌های 
                        مازندران و گیلان هستیم.
                    </p>
                    <div class="brand-features">
                        <span><i class="fas fa-check-circle"></i> اقامتگاه‌های تأییدشده</span>
                        <span><i class="fas fa-check-circle"></i> پشتیبانی ۲۴ ساعته</span>
                        <span><i class="fas fa-check-circle"></i> بهترین قیمت تضمینی</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ====== بخش ویژگی‌های خدمات ====== -->
    <section class="about-features">
        <div class="container">
            <h2 class="section-title text-center">چرا <span class="highlight">هتلک</span>؟</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-home"></i></div>
                    <h4>اقامتگاه‌های متنوع</h4>
                    <p>از سوئیت‌های لوکس و ویلاهای استخری تا کلبه‌های ییلاقی و خانه‌های سنتی</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <h4>امنیت و اعتماد</h4>
                    <p>تمام اقامتگاه‌ها توسط تیم ما بررسی و تأیید شده‌اند</p>
</div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-headset"></i></div>
                    <h4>پشتیبانی ۲۴ ساعته</h4>
                    <p>تیم پشتیبانی ما در تمام ساعات شبانه‌روز پاسخگوی شماست</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-wallet"></i></div>
                    <h4>بهترین قیمت</h4>
                    <p>ضمانت بهترین قیمت و پرداخت امن از طریق درگاه‌های معتبر</p>
                </div>
            </div>
        </div>
    </section>

<!-- ====== بخش آمار (حرفه‌ای با hover) ====== -->
<section class="about-stats">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item" data-count="80">
                <div class="stat-icon"><i class="fas fa-home"></i></div>
                <span class="stat-number">18</span>
                <span class="stat-label">اقامتگاه فعال</span>
                <div class="stat-hover-line"></div>
            </div>
            <div class="stat-item" data-count="420">
                <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                <span class="stat-number">420</span>
                <span class="stat-label">مسافر خوشحال</span>
                <div class="stat-hover-line"></div>
            </div>
            <div class="stat-item" data-count="98">
                <div class="stat-icon"><i class="fas fa-percent"></i></div>
                <span class="stat-number">98</span>
                <span class="stat-label">درصد رضایت</span>
                <div class="stat-hover-line"></div>
            </div>
            <div class="stat-item" data-count="24">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <span class="stat-number">24</span>
                <span class="stat-label">پشتیبانی ساعته</span>
                <div class="stat-hover-line"></div>
            </div>
        </div>
    </div>
</section>

    <!-- ====== بخش تماس (شبیه نمونه) ====== -->
    <section class="about-contact text-center">
        <div class="container">
            <h3>آماده‌ایم تا به شما کمک کنیم</h3>
            <p>برای رزرو اقامتگاه یا دریافت مشاوره، با ما تماس بگیرید</p>
            <div class="about-contact-buttons">
                <a href="tel:02112345678" class="btn btn-primary"><i class="fas fa-phone-alt"></i> تماس تلفنی</a>
                <a href="<?= BASE_URL ?>/contact.php" class="btn btn-outline-primary">فرم تماس</a>
            </div>
        </div>
    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statItems = document.querySelectorAll('.stat-item');
    
    // تابع شمارش
    function animateNumber(element) {
        const numberSpan = element.querySelector('.stat-number');
        if (!numberSpan) return;
        
        const target = parseInt(element.getAttribute('data-count'));
        if (isNaN(target) || target === 0) {
            numberSpan.textContent = target;
            return;
        }
        
        // اگر قبلاً شمارش کامل شده بود، دوباره از صفر شروع کن
        numberSpan.textContent = '0';
        
        let current = 0;
        const duration = 1200;
        const stepTime = 16;
        const totalSteps = duration / stepTime;
        const increment = target / totalSteps;
        
        function updateNumber() {
            current += increment;
            if (current >= target) {
                numberSpan.textContent = target;
                return;
            }
            numberSpan.textContent = Math.floor(current);
            requestAnimationFrame(updateNumber);
        }
        
        updateNumber();
    }
    
    // رویداد hover روی هر آیتم
    statItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            animateNumber(this);
        });
    });
    
    // همچنین برای اولین بار که بخش آمار دیده می‌شود، شمارش را شروع کن
    // (تا اگر کاربر هیچ hover نکرد، باز هم اعداد دیده شوند)
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const item = entry.target;
                // فقط یک بار شمارش کن، مگر اینکه hover کند
                const numberSpan = item.querySelector('.stat-number');
                if (numberSpan && numberSpan.textContent === '0') {
                    animateNumber(item);
                }
                observer.unobserve(item);
            }
        });
    }, { threshold: 0.3 });
    
    statItems.forEach(item => observer.observe(item));
});
</script>

<?php include 'includes/footer.php'; ?>