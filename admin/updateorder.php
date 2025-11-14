<?php
session_start();
include('../includes/auth_admin.php');
include("../includes/config.php");
include("../includes/header.php");

// require POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: orders.php');
    exit;
}

$status = isset($_POST['status']) ? $_POST['status'] : '';

$orderId = isset($_SESSION['orderId']) ? (int) $_SESSION['orderId'] : 0;
$upd = mysqli_prepare($conn, "UPDATE orderinfo SET status = ? WHERE orderinfo_id = ?");
$result = false;
if ($upd) {
    mysqli_stmt_bind_param($upd, 'si', $status, $orderId);
    $result = mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);
}
if ($result) {
    header("Location: orders.php");
}

// After updating, send email notification to customer
require_once __DIR__ . '/../includes/mail.php';

$orderId = isset($_SESSION['orderId']) ? (int) $_SESSION['orderId'] : 0;
if ($orderId && $result) {
    // fetch customer user email and order info
    $oq = "SELECT o.orderinfo_id, o.customer_id, o.shipping, o.status, c.user_id, u.email, c.lname, c.fname
            FROM orderinfo o
            JOIN customers c ON o.customer_id = c.customer_id
            JOIN users u ON c.user_id = u.id
            WHERE o.orderinfo_id = ? LIMIT 1";
    if ($stmt = $conn->prepare($oq)) {
        $stmt->bind_param('i', $orderId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $to = $row['email'];
            $statusHuman = ucfirst($row['status']);
            // fetch items
            $iq = "SELECT it.name, ol.quantity, it.sell_price 
                   FROM orderline ol 
                   JOIN items it ON ol.item_id = it.item_id 
                   WHERE ol.orderinfo_id = ?";
            $itemsHtml = '';
            $grand = 0.00;
            if ($istmt = $conn->prepare($iq)) {
                $istmt->bind_param('i', $orderId);
                $istmt->execute();
                $ires = $istmt->get_result();
                $itemsHtml .= '<table style="width:100%; border-collapse:collapse;">';
                $itemsHtml .= '<thead><tr><th style="text-align:left; border-bottom:1px solid #ddd;">Item</th><th style="text-align:right; border-bottom:1px solid #ddd;">Qty</th><th style="text-align:right; border-bottom:1px solid #ddd;">Price</th><th style="text-align:right; border-bottom:1px solid #ddd;">Total</th></tr></thead><tbody>';
                while ($ir = $ires->fetch_assoc()) {
                    $total = (float)$ir['sell_price'] * (int)$ir['quantity'];
                    $grand += $total;
                    $itemsHtml .= '<tr>';
                    $itemsHtml .= '<td style="padding:8px 0;">' . htmlspecialchars($ir['name']) . '</td>';
                    $itemsHtml .= '<td style="padding:8px 0; text-align:right;">' . (int)$ir['quantity'] . '</td>';
                    $itemsHtml .= '<td style="padding:8px 0; text-align:right;">' . number_format((float)$ir['sell_price'],2) . '</td>';
                    $itemsHtml .= '<td style="padding:8px 0; text-align:right;">' . number_format($total,2) . '</td>';
                    $itemsHtml .= '</tr>';
                }
                $itemsHtml .= '</tbody></table>';
                $istmt->close();
            }

            $shipping = isset($row['shipping']) ? (float)$row['shipping'] : 0.00;
            $grandTotal = $grand + $shipping;

            $html = '<h2>Order update — Order #' . (int)$row['orderinfo_id'] . '</h2>';
            $html .= '<p>Hi ' . htmlspecialchars($row['fname']) . ',</p>';
            $html .= '<p>Your order status has been updated to <strong>' . htmlspecialchars($statusHuman) . '</strong>.</p>';
            $html .= $itemsHtml;
            $html .= '<p style="text-align:right;">Subtotal: <strong>' . number_format($grand,2) . '</strong></p>';
            $html .= '<p style="text-align:right;">Shipping: <strong>' . number_format($shipping,2) . '</strong></p>';
            $html .= '<p style="text-align:right;">Grand total: <strong>' . number_format($grandTotal,2) . '</strong></p>';

            // send
            $subject = "Order #" . (int)$row['orderinfo_id'] . " status: " . $statusHuman;
            smtp_send_mail($to, $subject, $html);
        }
        $stmt->close();
    }
}
