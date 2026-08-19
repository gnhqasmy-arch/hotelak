<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once '../../config/database.php';
require_once '../../config/functions.php';

// ====== تنظیم کلاس بدنه ======
$body_class = 'quiz-start-page';

include '../../includes/header.php';
?>

<style>
    /* ====== پس‌زمینه تمام‌صفحه ====== */
    body.quiz-start-page {
        margin: 0 !important;
        padding: 0 !important;
        background-image: url('<?= BASE_URL ?>/images/MTI0NTQ0MzQ4.jpg') !important;
        background-size: cover !important;
        background-attachment: fixed !important;
        background-position: center !important;
        min-height: 100vh !important;
        display: flex !important;
        flex-direction: column !important;
        background-color: #e8d5c4 !important;
    }

    /* ====== کادر شیشه‌ای ====== */
    .quiz-start-wrapper {
        flex: 1 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 40px 20px !important;
        box-sizing: border-box !important;
        min-height: calc(100vh - 80px) !important;
    }

    .quiz-start-box {
        background: rgba(255, 212, 194, 0.43) !important;
        backdrop-filter: blur(18px) !important;
        -webkit-backdrop-filter: blur(18px) !important;
        border-radius: 40px !important;
        padding: 3rem 2.5rem !important;
        max-width: 600px !important;
        width: 100% !important;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.12) !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        text-align: center !important;
    }

    /* ====== حذف پس‌زمینه نارنجی از ربات ====== */
    .quiz-start-box .robot-icon {
        font-size: 3.9rem !important;
        display: block !important;
        margin-bottom: 2.2rem !important;
        line-height: 1 !important;
        background: transparent !important;      /* حذف هرگونه پس‌زمینه */
        box-shadow: none !important;            /* حذف سایه‌های رنگی */
        color: #1e2a3e !important;              /* رنگ آیکون (در صورت نیاز) */
    }

    .quiz-start-box h2 {
        color: #1e2a3e !important;
        font-weight: 800 !important;
        font-size: 2rem !important;
        margin-bottom: 0.5rem !important;
    }

    .quiz-start-box p {
        color: #4a3729 !important;
        font-size: 1.05rem !important;
        line-height: 1.8 !important;
        margin-bottom: 0.8rem !important;
    }

    .quiz-start-box .btn-start {
        background: linear-gradient(135deg, #ffb07c, #ff8a5a) !important;
        border: none !important;
        border-radius: 60px !important;
        padding: 0.8rem 2.8rem !important;
        font-size: 1.15rem !important;
        font-weight: 700 !important;
        color: #fff !important;
        box-shadow: 0 4px 15px rgba(255, 176, 124, 0.3) !important;
        transition: all 0.3s ease !important;
        text-decoration: none !important;
        display: inline-block !important;
        margin-top: 0.5rem !important;
    }

    .quiz-start-box .btn-start:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 10px 30px rgba(255, 176, 124, 0.4) !important;
    }

    @media (max-width: 576px) {
        .quiz-start-box {
            padding: 2rem 1.5rem !important;
        }
        .quiz-start-box .robot-icon {
            font-size: 3rem !important;
        }
        .quiz-start-box h2 {
            font-size: 1.5rem !important;
        }
    }
</style>

<div class="quiz-start-wrapper">
    <div class="quiz-start-box">
        <span class="robot-icon">🤖</span>
        <h2>✨ دستیار سفر هوشمند</h2>
        <p>
            با پاسخ به <strong>۲۰ سوال</strong> کوتاه، بهترین اقامتگاه متناسب با سلیقه، بودجه و نیازهای شما را پیشنهاد می‌دهیم.
        </p>
        <hr style="opacity: 0.15; margin: 1.2rem 0;">
        <!-- ====== لینک به صفحه سوالات (quiz_form.php) ====== -->
        <a href="<?= BASE_URL ?>/modules/quiz/quiz.php" class="btn-start">
            شروع پرسشنامه 🚀
        </a>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

