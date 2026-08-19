<?php
// تعریف ثابت BASE_URL (فقط یک بار)

    define('BASE_URL', 'http://localhost/reservation_db'); 

// مسیر پروژه خود را تنظیم کنید

$host = 'localhost';
$dbname = 'reservation_db'; // نام دیتابیس شما
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("خطا در اتصال به دیتابیس: " . $e->getMessage());
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>