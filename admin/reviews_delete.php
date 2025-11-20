<?php
session_start();
include('../includes/config.php');

// ✅ Admin-only access enforcement
if (!isset($_SESSION['user_id'])) {
    header("Location: ../user/login.php?error=unauthorized");
    exit();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?error=adminonly");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $del = mysqli_prepare($conn, "DELETE FROM reviews WHERE id = ?");
    if ($del) {
        mysqli_stmt_bind_param($del, 'i', $id);
        mysqli_stmt_execute($del);
        mysqli_stmt_close($del);
    }
}

// Redirect back to reviews list
header('Location: reviews.php');
exit();
?>
