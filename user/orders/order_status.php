<?php
session_start();
include('../../includes/auth_user.php');
include('../../includes/config.php');

$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$status   = isset($_POST['status']) ? $_POST['status'] : '';

if ($order_id && in_array($status, ['Cancelled', 'Delivered'])) {
    // Update order status
    $stmt = mysqli_prepare($conn, "UPDATE orderinfo SET status = ? WHERE orderinfo_id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $status, $order_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // ✅ If cancelled, restock items
    if ($status === 'Cancelled') {
        $lineRes = mysqli_query($conn, "SELECT item_id, quantity FROM orderline WHERE orderinfo_id = {$order_id}");
        while ($line = mysqli_fetch_assoc($lineRes)) {
            $item_id = (int)$line['item_id'];
            $qty     = (int)$line['quantity'];
            $updStock = mysqli_prepare($conn, "UPDATE stocks SET quantity = quantity + ? WHERE item_id = ?");
            mysqli_stmt_bind_param($updStock, 'ii', $qty, $item_id);
            mysqli_stmt_execute($updStock);
            mysqli_stmt_close($updStock);
        }
    }
}

header("Location: orders.php");
exit;
