<!-- ============================================ -->
<!-- فوتر تمام‌عرض با کلاس‌های مناسب (بدون استایل) -->
<!-- ============================================ -->
<footer class="footer-main">
    <div class="footer-inner">
        <div class="footer-grid">
            
            <!-- ستون 1: درباره ما -->
            <div class="footer-col fade-in-up">
                <div class="footer-card">
                    <h5 class="footer-title">درباره هتلک</h5>
                    <p class="footer-desc">
                        سامانه هوشمند رزرو اقامتگاه‌های شمال ایران با بهترین قیمت و پشتیبانی ۲۴ ساعته.
                    </p>
                    <div class="footer-contact">
                        <p><i class="fas fa-phone-alt"></i> ۰۲۱-۱۲۳۴۵۶۷۸</p>
                        <p><i class="fas fa-envelope"></i> info@suitereserve.com</p>
                    </div>
                </div>
            </div>

            <!-- ستون 2: لینک‌های سریع -->
            <div class="footer-col fade-in-up" style="animation-delay: 0.1s;">
                <div class="footer-card">
                    <h5 class="footer-title">دسترسی سریع</h5>
                    <ul class="footer-links">
                        <li><a href="<?= BASE_URL ?>/index.php">صفحه اصلی</a></li>
                        <li><a href="<?= BASE_URL ?>/modules/accommodation/list.php?type=سوییت">سوییت‌ها</a></li>
                        <li><a href="<?= BASE_URL ?>/modules/accommodation/list.php?type=ویلا">ویلاها</a></li>
                        <li><a href="<?= BASE_URL ?>/modules/quiz/start.php">دستیار سفر</a></li>
                        <li><a href="<?= BASE_URL ?>/about.php">درباره ما</a></li>
                    </ul>
                </div>
            </div>

            <!-- ستون 3: آمار و افتخارات -->
            <div class="footer-col fade-in-up" style="animation-delay: 0.2s;">
                <div class="footer-card">
                    <h5 class="footer-title">افتخارات ما</h5>
                    <div class="footer-stats">
                        <div class="stat-box">
                            <i class="fas fa-home"></i>
                            <span class="stat-number">18</span>
                            <span class="stat-label">اقامتگاه</span>
                        </div>
                        <div class="stat-box">
                            <i class="fas fa-user-check"></i>
                            <span class="stat-number">۴۲۰</span>
                            <span class="stat-label">مسافر خوشحال</span>
                        </div>
                        <div class="stat-box">
                            <i class="fas fa-star"></i>
                            <span class="stat-number">۴.۹</span>
                            <span class="stat-label">میانگین امتیاز</span>
                        </div>
                    </div>
                    <div class="footer-social">
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-telegram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>

        </div>

        <!-- کپی‌رایت -->
        <div class="footer-copy">
            <p> ۱۴۰۴ - طراحی و توسعه با تمامی حقوق برای سوییت‌رزرو محفوظ است. |  </p>
        </div>
    </div>
</footer>
<script></script>
<!-- دستیار سفر شناور (ربات با tooltip کشویی) -->
<div class="assistant-wrapper">
    <a href="<?= BASE_URL ?>/modules/quiz/start.php" class="robot-assistant">
        <div class="robot-icon">
            🤖
        </div>
        <span class="assistant-tooltip">دستیار برنامه ریزی سفر</span>
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- ====== اسکریپت فعال‌سازی تقویم شمسی برای فیلدهای تاریخ ====== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ====== تقویم شمسی برای فیلدهای تاریخ ورود و خروج ======
    var datepickers = document.querySelectorAll('.datepicker');
    
    if (datepickers.length > 0 && typeof $.fn.persianDatepicker !== 'undefined') {
        datepickers.forEach(function(input) {
            $(input).persianDatepicker({
                format: 'YYYY/MM/DD',
                autoClose: true,
                initialValue: false,
                calendar: {
                    type: 'persian'  // شمسی
                },
                onSelect: function() {
                    $(input).persianDatepicker('hide');
                }
            });
            
            // باز کردن تقویم با کلیک روی فیلد
            input.addEventListener('click', function() {
                $(this).persianDatepicker('show');
            });
            
            // بستن تقویم با کلیک خارج از آن
            document.addEventListener('click', function(e) {
                var picker = $(input).data('persianDatepicker');
                if (!picker || !picker._isOpen) return;
                
                var target = e.target;
                var isClickInside = 
                    target === input ||
                    $(target).closest('.persian-datepicker-container').length > 0 ||
                    $(target).closest('.persian-datepicker-holder').length > 0;
                
                if (!isClickInside) {
                    $(input).persianDatepicker('hide');
                }
            });
        });
    }
});

// ====== مدیریت منوی مرتب‌سازی ======
function toggleSortDropdown() {
    var dropdown = document.getElementById('sortDropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

// بستن منو با کلیک خارج از آن
document.addEventListener('click', function(e) {
    var wrapper = document.querySelector('.sort-filter-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        var dropdown = document.getElementById('sortDropdown');
        if (dropdown) {
            dropdown.classList.remove('show');
        }
    }
});
function toggleSortMenu() {
    var menu = document.getElementById('sortMenu');
    if (menu) {
        menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
    }
}
document.addEventListener('click', function(e) {
    var dropdown = document.querySelector('.sort-dropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        var menu = document.getElementById('sortMenu');
        if (menu) menu.style.display = 'none';
    }
});
</script>
</body>
</html>