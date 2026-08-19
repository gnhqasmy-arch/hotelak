<?php
session_start();
unset($_SESSION['discount_applied']);
echo 'سشن تخفیف با موفقیت پاک شد. <a href="index.php">بازگشت به صفحه اصلی</a>';