<?php
session_start();
include('../includes/auth_user.php');
include('../includes/config.php');

$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

if ($order_id && in_array($status, ['Cancelled', 'Delivered'])) {
    $stmt = mysqli_prepare($conn, "UPDATE orderinfo SET status = ? WHERE orderinfo_id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $status, $order_id);
    mysqli_stmt_execute($stmt);
}

header("Location: orders.php");
exit;
