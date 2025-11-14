<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/config.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id) {
    $del = mysqli_prepare($conn, "DELETE FROM reviews WHERE id = ?");
    if ($del) {
        mysqli_stmt_bind_param($del, 'i', $id);
        mysqli_stmt_execute($del);
        mysqli_stmt_close($del);
    }
}
header('Location: reviews.php');
exit();
?>
