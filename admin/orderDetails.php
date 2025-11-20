<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// ✅ Admin-only access enforcement
if (!isset($_SESSION['user_id'])) {
    header("Location: ../user/login.php?error=unauthorized");
    exit();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?error=adminonly");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)$_POST['order_id'];
    $status  = mysqli_real_escape_string($conn, $_POST['status']);

    try {
        // Start transaction
        $conn->begin_transaction();

        // Update order status
        $sql = "UPDATE orderinfo SET status = '$status' WHERE orderinfo_id = $orderId";
        if (!mysqli_query($conn, $sql)) {
            throw new Exception("Failed to update order status");
        }

        // Example: If status is "Shipped", deduct inventory
        if ($status === 'Shipped') {
            $sqlItems = "SELECT item_id, quantity FROM orderline WHERE orderinfo_id = $orderId";
            $itemsRes = mysqli_query($conn, $sqlItems);

            if (!$itemsRes) {
                throw new Exception("Failed to fetch order items");
            }

            while ($item = mysqli_fetch_assoc($itemsRes)) {
                $itemId   = (int)$item['item_id'];
                $quantity = (int)$item['quantity'];

                $updateStock = "UPDATE item SET stock = stock - $quantity WHERE item_id = $itemId";
                if (!mysqli_query($conn, $updateStock)) {
                    throw new Exception("Failed to update stock for item $itemId");
                }
            }
        }

        // Commit transaction if all queries succeed
        $conn->commit();
        header("Location: orderDetails.php?id=$orderId&success=updated");
        exit();

    } catch (Exception $e) {
        // Rollback transaction if any query fails
        $conn->rollback();
        header("Location: orderDetails.php?id=$orderId&error=updatefailed");
        exit();
    }
}
?>
