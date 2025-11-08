<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_admin.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = 'Invalid request method.';
    header('Location: users.php');
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id']) || !isset($_POST['role'])) {
    $_SESSION['message'] = 'Missing parameters.';
    header('Location: users.php');
    exit;
}

$user_id = (int) $_POST['id'];
$role = trim($_POST['role']);
$allowed = ['customer', 'admin'];
if (!in_array($role, $allowed, true)) {
    $_SESSION['message'] = 'Invalid role.';
    header('Location: users.php');
    exit;
}

$sql = "UPDATE users SET role = ? WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('si', $role, $user_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = 'User role updated.';
        // if admin changed their own role, update session immediately
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
            $_SESSION['role'] = $role;
        }
    } else {
        $_SESSION['message'] = 'Failed to update user role.';
    }
    $stmt->close();
} else {
    $_SESSION['message'] = 'Database error.';
}

header('Location: users.php');
exit;
