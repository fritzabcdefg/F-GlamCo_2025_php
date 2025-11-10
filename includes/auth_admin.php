<?php
// admin auth check - include at top of admin/CRUD pages before any output
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// user must be logged in and have role 'admin'
if (empty($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    require_once __DIR__ . '/flash.php';
    flash_set('Please log in as admin to access that page.', 'warning');
    header('Location: /F&LGlamCo/user/login.php');
    exit();
}

?>
