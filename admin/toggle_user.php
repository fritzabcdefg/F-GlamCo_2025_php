<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_admin.php';

session_start();

// require POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: users.php');
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header('Location: users.php');
    exit;
}

$user_id = (int) $_POST['id'];

// fetch current active value
$sql = "SELECT active FROM users WHERE id = ? LIMIT 1";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($active);
    if ($stmt->fetch() === null) {
        // no user found
        $stmt->close();
        header('Location: users.php');
        exit;
    }
    $stmt->close();
} else {
    header('Location: users.php');
    exit;
}

$new_active = $active ? 0 : 1;

$update = "UPDATE users SET active = ? WHERE id = ?";
if ($ustmt = $conn->prepare($update)) {
    $ustmt->bind_param('ii', $new_active, $user_id);
    $ustmt->execute();
    $ustmt->close();
}

header('Location: users.php');
exit;
