<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth_admin.php';

require_once __DIR__ . '/../includes/flash.php';

// require POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash_set('Invalid request method.', 'danger');
    header('Location: users.php');
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    flash_set('Invalid user id.', 'danger');
    header('Location: users.php');
    exit;
}

// CSRF
if (!isset($_POST['csrf_token']) || !csrf_verify($_POST['csrf_token'])) {
    flash_set('Invalid form submission.', 'danger');
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
        flash_set('User not found.', 'danger');
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
        flash_set($new_active ? 'User activated.' : 'User deactivated.', 'success');
    } else {
        flash_set('Failed to update user status.', 'danger');
    }
    $ustmt->close();
} else {
    $_SESSION['message'] = 'Database error.';
}

header('Location: users.php');
exit;
