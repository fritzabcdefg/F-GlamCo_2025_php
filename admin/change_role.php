<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../includes/flash.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash_set('Invalid request method.', 'danger');
    header('Location: users.php');
    exit;
}
// CSRF check
if (!isset($_POST['csrf_token']) || !csrf_verify($_POST['csrf_token'])) {
    flash_set('Invalid form submission.', 'danger');
    header('Location: users.php');
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id']) || !isset($_POST['role'])) {
    flash_set('Missing parameters.', 'danger');
    header('Location: users.php');
    exit;
}

$user_id = (int) $_POST['id'];
$role = trim($_POST['role']);
$allowed = ['customer', 'admin'];
if (!in_array($role, $allowed, true)) {
    flash_set('Invalid role.', 'danger');
    header('Location: users.php');
    exit;
}

$sql = "UPDATE users SET role = ? WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('si', $role, $user_id);
    if ($stmt->execute()) {
        flash_set('User role updated.', 'success');
        // if admin changed their own role, update session immediately
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
            $_SESSION['role'] = $role;
        }
    } else {
        flash_set('Failed to update user role.', 'danger');
    }
    $stmt->close();
} else {
    flash_set('Database error.', 'danger');
}

header('Location: users.php');
exit;
