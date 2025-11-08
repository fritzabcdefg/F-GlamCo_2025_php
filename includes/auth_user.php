<?php
// user auth check - include at top of pages that require any logged-in user
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    $_SESSION['message'] = 'Please log in to continue.';
    header('Location: /F&LGlamCo/user/login.php');
    exit();
}

?>
