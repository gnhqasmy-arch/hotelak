<?php
if (session_status() == PHP_SESSION_NONE) session_start();
?>

<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>

    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سیستم رزرو سوییت، ویلا و هتل | شمال ایران</title>
    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
<!-- کتابخانه‌های تاریخ شمسی/قمری -->
<link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
<script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
       <!-- یا اگر BASE_URL به درستی تعریف شده -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
     
    <style>
        :root {
            --primary: #ff6b35;
            --secondary: #2c3e50;
            --dark: #1e2a3e;
        }
        body {
            font-family: 'IRANSans', 'Tahoma', sans-serif;
            background: #f4f7fb;
        }
        /* نوار ناوبری */
        .navbar {
            background: var(--dark) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 0.8rem 0;
        }
        .navbar-brand {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary) !important;
        }
        .nav-link {
            color: white !important;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: 0.3s;
        }
        .nav-link:hover, .dropdown-item:hover {
            color: var(--primary) !important;
        }
        .dropdown-menu {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .btn-outline-light:hover {
            background: var(--primary);
            border-color: var(--primary);
        }
        /* بخش جستجو در هدر */
        .search-header {
            background: linear-gradient(135deg, var(--secondary), var(--dark));
            padding: 0.8rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .search-form {
            background: white;
            border-radius: 50px;
            padding: 0.3rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .search-form .form-control, .search-form .form-select {
            border: none;
            border-radius: 50px;
            padding: 0.6rem 1rem;
        }
        .search-form button {
            border-radius: 50px;
            background: var(--primary);
            border: none;
            padding: 0.6rem 1.5rem;
            font-weight: bold;
        }
        @media (max-width: 768px) {
            .search-form { border-radius: 15px; }
            .search-form .form-control, .search-form .form-select { border-radius: 10px; margin-bottom: 0.5rem; }
        }
        /* کارت اقامتگاه */
        .accommodation-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            background: white;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .accommodation-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 30px rgba(0,0,0,0.15);
        }
        .price-badge {
            background: var(--primary);
            color: white;
            padding: 5px 12px;
            border-radius: 30px;
            font-weight: bold;
            font-size: 1rem;
        }
        .footer {
            background: var(--dark);
            color: #ccc;
            padding: 2.5rem 0 1rem;
            margin-top:3rem;
        }
        .footer a {
            color: #ffc107;
            text-decoration: none;
        }

        /* دکمه‌های ورود/ثبت‌نام (همان استایل قبلی شما) */
        .auth-buttons .btn-login,
        .auth-buttons .btn-register {
display: inline-block;
            padding: 6px 18px;
            border-radius: 30px;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.85rem;
            background: transparent;
            border: 1px solid #ff6b35;
            color: #ff6b35;
            cursor: pointer;
        }
        .auth-buttons .btn-login:hover {
            background: #ff6b35;
            color: white;
            

        }
        .auth-buttons .btn-register {
            background: #ff6b35;
            color: white;
            border: none;
        }
        .auth-buttons .btn-register:hover {
            background: #e55a2b;
            transform: translateY(-2px);
        }
        /* در موبایل */
        @media (max-width: 768px) {
            .auth-buttons .btn-login,
            .auth-buttons .btn-register {
                width: 80%;
                text-align: center;
                margin: 5px auto;
                display: block;
            }
        }

        /* مودال‌های شیشه‌ای */
        .modal-content.glass-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 28px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
        .modal-header {
            border-bottom: none;
            padding-bottom: 0;
        }
        .modal-header .btn-close {
            background: rgba(0,0,0,0.05);
            border-radius: 50%;
            padding: 8px;
        }
    </style>
</head>
<body class="<?= isset($body_class) ? $body_class : '' ?>">

<!-- ====== نوار ناوبری ====== -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
            <i class="fas fa-home"></i> هتلک
        </a>
        
        <button class="navbar-toggler d-xl-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span style="color:white; font-size:1.8rem">☰</span>
        </button>
        
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php">صفحه اصلی</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/modules/accommodation/list.php">اقامتگاه ها</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/about.php">درباره ما</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/contact.php">تماس با ما</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/modules/quiz/start.php"> دستیار سفر</a></li>
            </ul>
            
            <!-- دکمه‌های ورود/ثبت‌نام با مودال -->
            <ul class="navbar-nav auth-buttons">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/modules/user/profile.php"><i class="fas fa-user"></i> <?= isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'کاربر' ?></a></li>
                 <!-- دکمه خروج با مودال -->
<li class="nav-item">
    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
        خروج <i class="fas fa-sign-out-alt"></i>
    </a>
</li>
                <?php else: ?>
                    <li class="nav-item">
                        <button class="btn-login" data-bs-toggle="modal" data-bs-target="#loginModal">ورود</button>
                    </li>
                    <li class="nav-item">
                        <button class="btn-register" data-bs-toggle="modal" data-bs-target="#registerModal">ثبت‌نام</button>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

         <!-- ====== مرتب‌سازی با آیکون کشویی ====== -->
<?php if (!isset($hide_search) || !$hide_search): ?>
<div class="search-header">
    <div class="container">
        <form action="<?= BASE_URL ?>/search_results.php" method="GET" class="row g-2 search-form align-items-center">

            <!-- ====== 2. نوع اقامتگاه (در کنار دکمه مرتب‌سازی) ====== -->
       <?php
$types = $pdo->query("SELECT DISTINCT accommodation_type FROM accommodations ORDER BY accommodation_type")->fetchAll(PDO::FETCH_COLUMN);
?>
<div style="display: flex; gap: 5px; align-items: center; flex-wrap: wrap;">
    <span style="font-weight: bold; margin-left: 5px;">نوع:</span>
    
    <?php foreach ($types as $t): ?>
        <label style="background: #f0f2f5; padding: 4px 12px; border-radius: 30px;">
            <input type="radio" name="type" value="<?= $t ?>" <?= (isset($_GET['type']) && $_GET['type'] == $t) ? 'checked' : '' ?>> 
            <?= $t ?>
        </label>
    <?php endforeach; ?>

    <label style="background: #f0f2f5; padding: 4px 12px; border-radius: 30px;">
        <input type="radio" name="type" value="" <?= (!isset($_GET['type']) || $_GET['type'] == '') ? 'checked' : '' ?>> 
        همه موارد
    </label>
</div>

            <!-- ====== تاریخ ورود (تقویم شمسی) ====== -->
            <div class="col-md-2">
                <input type="text" name="check_in" class="form-control datepicker search-input" placeholder="📅 تاریخ ورود" value="<?php echo isset($_GET['check_in']) ? htmlspecialchars($_GET['check_in']) : ''; ?>" autocomplete="off">
            </div>

             <!-- ====== تاریخ خروج (تقویم شمسی) ====== -->
            <div class="col-md-2">
                <input type="text" name="check_out" class="form-control datepicker search-input" placeholder="📅 تاریخ خروج" value="<?php echo isset($_GET['check_out']) ? htmlspecialchars($_GET['check_out']) : ''; ?>" autocomplete="off">
            </div>
                

           <!-- ====== تعداد مهمان ====== -->
<div class="col-md-2">
<input type="text" name="guests" class="form-control search-input guests-input" placeholder="👤 تعداد مهمان" value="<?php echo (isset($_GET['guests']) && $_GET['guests'] != '') ? (int)$_GET['guests'] : ''; ?>"></div>

            <!-- ====== دکمه جستجو ====== -->
           <div class="col-md-1 d-flex justify-content-center">
           <button type="submit" class="btn btn-search w-85"><i class="fas fa-search"></i> جستجو</button>
           </div>
       


        </form>
    </div>
</div>
<?php endif; ?>

<main class="container">
<!-- ====== مودال‌های ورود و ثبت‌نام (در اینجا یا انتهای صفحه) ====== -->

<!-- مودال ورود -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card">
            <div class="modal-header border-0">
                <h5 class="modal-title">🔐 ورود به حساب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="<?= BASE_URL ?>/modules/user/login.php" method="POST" class="auth-form">
                    <input type="hidden" name="redirect" value="<?= $_SERVER['REQUEST_URI'] ?>">
                    <div class="form-group">
                        <label>ایمیل</label>
                        <input type="email" name="email" class="form-control" placeholder="ایمیل خود را وارد کنید" required>
                    </div>
                    <div class="form-group">
                        <label>رمز عبور</label>
                        <input type="password" name="password" class="form-control" placeholder="رمز عبور خود را وارد کنید" required>
                    </div>
                    <!-- ====== دکمه ورود (اضافه شد) ====== -->
                    <button type="submit" class="btn btn-primary w-100 mt-2">ورود</button>
                </form>
                <p class="text-center mt-3">
                    حساب ندارید؟ <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">ثبت‌نام کنید</a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- ====== مودال تأیید خروج ====== -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card">
            <div class="modal-header border-0">
                <h5 class="modal-title">⚠️ تأیید خروج</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-question-circle" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;"></i>
                <h5>آیا از خروج از حساب کاربری خود مطمئن هستید؟</h5>
                <p class="text-muted">پس از خروج، برای دسترسی به حساب خود باید دوباره وارد شوید.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">انصراف</button>
                <a href="<?= BASE_URL ?>/modules/user/logout.php" class="btn btn-danger">بله، خروج</a>
            </div>
        </div>
    </div>
</div>
<!-- مودال ثبت‌نام -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card">
            <div class="modal-header border-0">
                <h5 class="modal-title">📝 ثبت‌نام</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="<?= BASE_URL ?>/modules/user/register.php" method="POST" class="auth-form" id="registerForm">
                    <input type="hidden" name="from_modal" value="1">
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
                 <div class="form-group">
    <label>رمز عبور</label>
    <input type="password" name="password" class="form-control" placeholder="حداقل ۸ کاراکتر" required>
    <small class="text-muted" style="display: block; margin-top: 5px;">
        رمز باید شامل حرف بزرگ، حرف کوچک، عدد و کاراکتر خاص باشد.
    </small>
</div>
<div class="form-group">
    <label>تکرار رمز عبور</label>
    <input type="password" name="confirm_password" class="form-control" placeholder="رمز را دوباره وارد کنید" required>
</div>

                    <button type="submit" class="btn btn-primary w-100 mt-2">ثبت‌نام</button>
                </form>
                <p class="text-center mt-3">
                    قبلاً حساب دارید؟ <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">وارد شوید</a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- پایان main container (در footer بسته می‌شود) -->