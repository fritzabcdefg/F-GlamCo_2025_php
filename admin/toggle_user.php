<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_admin.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = 'Invalid user id.';
    header('Location: users.php');
    exit;
}

$user_id = (int) $_GET['id'];

// fetch current active value
$sql = "SELECT active FROM users WHERE id = ? LIMIT 1";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($active);
    if ($stmt->fetch() === null) {
        // no user found
        $_SESSION['message'] = 'User not found.';
        $stmt->close();
        header('Location: users.php');
        exit;
    }
    $stmt->close();
} else {
    $_SESSION['message'] = 'Database error.';
    header('Location: users.php');
    exit;
}

$new_active = $active ? 0 : 1;

$update = "UPDATE users SET active = ? WHERE id = ?";
if ($ustmt = $conn->prepare($update)) {
    $ustmt->bind_param('ii', $new_active, $user_id);
    if ($ustmt->execute()) {
        $_SESSION['message'] = $new_active ? 'User activated.' : 'User deactivated.';
    } else {
        $_SESSION['message'] = 'Failed to update user status.';
    }
    $ustmt->close();
} else {
    $_SESSION['message'] = 'Database error.';
}

header('Location: users.php');
exit;
