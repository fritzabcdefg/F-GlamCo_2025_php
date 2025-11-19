<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: users.php');
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id']) || !isset($_POST['role'])) {
    header('Location: users.php');
    exit;
}

$user_id = (int) $_POST['id'];
$role = trim($_POST['role']);
$allowed = ['customer', 'admin'];
if (!in_array($role, $allowed, true)) {
    header('Location: users.php');
    exit;
}

$sql = "UPDATE users SET role = ? WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('si', $role, $user_id);
    if ($stmt->execute()) {
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
            $_SESSION['role'] = $role;
        }
    }
    $stmt->close();
}

header('Location: users.php');
exit;
