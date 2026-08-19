<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';

// پاک کردن سشن
$_SESSION = array();
session_destroy();

// هدایت به صفحه اصلی
redirect('../../index.php');