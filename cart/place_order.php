<?php
session_start();
include('../includes/auth_user.php');
include('../includes/header.php');
include('../includes/config.php');
include('../includes/mail.php'); 

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../index.php');
        exit;
    }

    mysqli_query($conn, 'START TRANSACTION');

    // --- Get customer_id and email ---
    $customer_id = null;
    $customer_email = null;

    // Try customers table first
    $selCust = mysqli_prepare($conn, "
        SELECT c.customer_id, u.email 
        FROM customers c
        INNER JOIN users u ON c.user_id = u.id
        WHERE c.user_id = ? LIMIT 1
    ");
    mysqli_stmt_bind_param($selCust, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($selCust);
    mysqli_stmt_bind_result($selCust, $customer_id, $customer_email);
    mysqli_stmt_fetch($selCust);
    mysqli_stmt_close($selCust);

    // Fallback: if no customer row, get email directly from users table
    if (empty($customer_email)) {
        $selUser = mysqli_prepare($conn, "SELECT email FROM users WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($selUser, 'i', $_SESSION['user_id']);
        mysqli_stmt_execute($selUser);
        mysqli_stmt_bind_result($selUser, $customer_email);
        mysqli_stmt_fetch($selUser);
        mysqli_stmt_close($selUser);
    }

    // --- Insert into orderinfo ---
    $shipping = 80.00;
    $stmt1 = mysqli_prepare($conn, 'INSERT INTO orderinfo(customer_id, date_placed, date_shipped, shipping) VALUES (?, NOW(), NOW(), ?)');
    mysqli_stmt_bind_param($stmt1, 'id', $customer_id, $shipping);
    mysqli_stmt_execute($stmt1);
    $orderinfo_id = mysqli_insert_id($conn);

    // --- Prepare reusable statements ---
    $stmt2 = mysqli_prepare($conn, 'INSERT INTO orderline(orderinfo_id, item_id, quantity) VALUES (?, ?, ?)');
    $stmt3 = mysqli_prepare($conn, 'UPDATE stocks SET quantity = quantity - ? WHERE item_id = ?');

    // --- Insert order lines and update stock ---
    foreach ($_SESSION["cart_products"] as $cart_itm) {
        $product_code = $cart_itm["item_id"];
        $product_qty  = $cart_itm["item_qty"];

        mysqli_stmt_bind_param($stmt2, 'iii', $orderinfo_id, $product_code, $product_qty);
        mysqli_stmt_execute($stmt2);

        mysqli_stmt_bind_param($stmt3, 'ii', $product_qty, $product_code);
        mysqli_stmt_execute($stmt3);
    }

    // ✅ Commit transaction
    mysqli_commit($conn);

    // --- Fetch customer info for email ---
    $oq = "SELECT o.orderinfo_id, o.customer_id, o.shipping, o.status, c.user_id, u.email, c.lname, c.fname
            FROM orderinfo o
            JOIN customers c ON o.customer_id = c.customer_id
            JOIN users u ON c.user_id = u.id
            WHERE o.orderinfo_id = ? LIMIT 1";
    if ($stmt = $conn->prepare($oq)) {
        $stmt->bind_param('i', $orderinfo_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $to = $row['email'];
            $fname = $row['fname'];
            $shipping = (float)$row['shipping'];

            // --- Build items table ---
            $iq = "SELECT it.name, ol.quantity, it.sell_price 
                   FROM orderline ol 
                   JOIN items it ON ol.item_id = it.item_id 
                   WHERE ol.orderinfo_id = ?";
            $itemsHtml = '';
            $grand = 0.00;
            if ($istmt = $conn->prepare($iq)) {
                $istmt->bind_param('i', $orderinfo_id);
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
                    $itemsHtml .= '<td style="padding:8px 0; text-align:right;">₱' . number_format((float)$ir['sell_price'],2) . '</td>';
                    $itemsHtml .= '<td style="padding:8px 0; text-align:right;">₱' . number_format($total,2) . '</td>';
                    $itemsHtml .= '</tr>';
                }
                $itemsHtml .= '</tbody></table>';
                $istmt->close();
            }

            $grandTotal = $grand + $shipping;

            // --- Compose email ---
            $html = '<h2>Order Confirmation — Order #' . (int)$row['orderinfo_id'] . '</h2>';
            $html .= '<p>Hi ' . htmlspecialchars($fname) . ',</p>';
            $html .= '<p>Thank you for your order! Here are your items:</p>';
            $html .= $itemsHtml;
            $html .= '<p style="text-align:right;">Subtotal: <strong>₱' . number_format($grand,2) . '</strong></p>';
            $html .= '<p style="text-align:right;">Shipping: <strong>₱' . number_format($shipping,2) . '</strong></p>';
            $html .= '<p style="text-align:right;">Grand total: <strong>₱' . number_format($grandTotal,2) . '</strong></p>';

            // Send customer confirmation only if we have an address
            if (!empty($to)) {
                $customerSubject = "Order #" . (int)$orderinfo_id . " Confirmation";
                smtp_send_mail($to, $customerSubject, $html);
            }

                        // Always send admin notification
            $adminSubject = "New Order Placed";
            smtp_send_mail("inbox@YOURID.mailtrap.io", $adminSubject, $html);

        }
        $stmt->close();
    }

    unset($_SESSION['cart_products']);

    header('Location: ../thank_you.php');
    exit;

} catch (mysqli_sql_exception $e) {
    echo "<div class='alert alert-danger text-center mt-4'>Error: " . $e->getMessage() . "</div>";
    mysqli_rollback($conn);
}

include('../includes/footer.php');
?>
