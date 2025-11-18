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

    foreach ($_SESSION["cart_products"] as $cart_itm) {
        $product_code = $cart_itm["item_id"];
        $product_qty  = $cart_itm["item_qty"];

        mysqli_stmt_bind_param($stmt2, 'iii', $orderinfo_id, $product_code, $product_qty);
        mysqli_stmt_execute($stmt2);

        mysqli_stmt_bind_param($stmt3, 'ii', $product_qty, $product_code);
        mysqli_stmt_execute($stmt3);
    }

    mysqli_commit($conn);
    // --- Build email using orderdetails view ---
    $iq = "SELECT fname, lname, addressline, town, zipcode, phone, status, name, quantity, sell_price 
           FROM orderdetails 
           WHERE orderinfo_id = ?";
    $itemsHtml = '';
    $grand = 0.00;
    $customerInfo = '';
    if ($istmt = $conn->prepare($iq)) {
        $istmt->bind_param('i', $orderinfo_id);
        $istmt->execute();
        $ires = $istmt->get_result();

        if ($first = $ires->fetch_assoc()) {
            $to = $customer_email;
            $fname = $first['fname'];

            // Customer info block
            $customerInfo .= '<table style="width:100%; font-size:14px; margin-bottom:20px;">';
            $customerInfo .= '<tr><td style="padding:6px;"><strong>Name:</strong></td><td>' . htmlspecialchars($first['fname'] . ' ' . $first['lname']) . '</td></tr>';
            $customerInfo .= '<tr><td style="padding:6px;"><strong>Shipping Address:</strong></td><td>' . htmlspecialchars($first['addressline']) . ', ' . htmlspecialchars($first['town']) . ' ' . htmlspecialchars($first['zipcode']) . '</td></tr>';
            $customerInfo .= '<tr><td style="padding:6px;"><strong>Phone:</strong></td><td>' . htmlspecialchars($first['phone']) . '</td></tr>';
            $customerInfo .= '<tr><td style="padding:6px;"><strong>Status:</strong></td><td>' . htmlspecialchars($first['status']) . '</td></tr>';
            $customerInfo .= '</table>';

            // Items table
            $itemsHtml .= '<table style="width:100%; border-collapse:collapse; border:1px solid #ccc;">';
            $itemsHtml .= '<thead><tr style="background:#f9f9f9;">
                <th style="text-align:left; padding:8px; border:1px solid #ccc;">Item</th>
                <th style="text-align:right; padding:8px; border:1px solid #ccc;">Qty</th>
                <th style="text-align:right; padding:8px; border:1px solid #ccc;">Price</th>
                <th style="text-align:right; padding:8px; border:1px solid #ccc;">Total</th>
            </tr></thead><tbody>';
            // First item row
            $total = (float)$first['sell_price'] * (int)$first['quantity'];
            $grand += $total;
            $itemsHtml .= '<tr>
                <td style="padding:8px; border:1px solid #ccc;">' . htmlspecialchars($first['name']) . '</td>
                <td style="padding:8px; text-align:right; border:1px solid #ccc;">' . (int)$first['quantity'] . '</td>
                <td style="padding:8px; text-align:right; border:1px solid #ccc;">₱' . number_format((float)$first['sell_price'],2) . '</td>
                <td style="padding:8px; text-align:right; border:1px solid #ccc;">₱' . number_format($total,2) . '</td>
            </tr>';

            // Remaining items
            while ($ir = $ires->fetch_assoc()) {
                $total = (float)$ir['sell_price'] * (int)$ir['quantity'];
                $grand += $total;
                $itemsHtml .= '<tr>
                    <td style="padding:8px; border:1px solid #ccc;">' . htmlspecialchars($ir['name']) . '</td>
                    <td style="padding:8px; text-align:right; border:1px solid #ccc;">' . (int)$ir['quantity'] . '</td>
                    <td style="padding:8px; text-align:right; border:1px solid #ccc;">₱' . number_format((float)$ir['sell_price'],2) . '</td>
                    <td style="padding:8px; text-align:right; border:1px solid #ccc;">₱' . number_format($total,2) . '</td>
                </tr>';
            }

            $itemsHtml .= '</tbody></table>';
            $istmt->close();

            $grandTotal = $grand + $shipping;

            // Compose styled email
            $html = '<div style="font-family:Arial, sans-serif; font-size:14px; color:#333;">';
            $html .= '<h2 style="color:#e83e8c;">Order Confirmation — Order #' . (int)$orderinfo_id . '</h2>';
            $html .= '<p>Hi <strong>' . htmlspecialchars($fname) . '</strong>,</p>';
            $html .= '<p>Thank you for your order! Below are your order details:</p>';
            $html .= '<hr style="border:0; border-top:1px solid #ccc;">';
            $html .= '<h4 style="margin-bottom:5px;">Shipping Information</h4>';
            $html .= $customerInfo;
            $html .= '<h4 style="margin-bottom:5px;">Items Ordered</h4>';
            $html .= $itemsHtml;
            $html .= '<table style="width:100%; margin-top:20px; font-size:15px;">';
            $html .= '<tr><td style="text-align:right; padding:6px;">Subtotal:</td><td style="text-align:right; padding:6px;"><strong>₱' . number_format($grand,2) . '</strong></td></tr>';
            $html .= '<tr><td style="text-align:right; padding:6px;">Shipping:</td><td style="text-align:right; padding:6px;"><strong>₱' . number_format($shipping,2) . '</strong></td></tr>';
            $html .= '<tr><td style="text-align:right; padding:6px;">Grand Total:</td><td style="text-align:right; padding:6px;"><strong>₱' . number_format($grandTotal,2) . '</strong></td></tr>';
            $html .= '</table>';
            $html .= '<hr style="border:0; border-top:1px solid #ccc; margin-top:30px;">';
            $html .= '<p style="font-size:13px; color:#666;">If you have any questions, feel free to reply to this email. Thank you for shopping with <strong>F & L Glam Co</strong>!</p>';
            $html .= '</div>';
            // Send customer confirmation
            if (!empty($to)) {
                $customerSubject = "Order #" . (int)$orderinfo_id . " Placed";
                smtp_send_mail($to, $customerSubject, $html);
            }

            // Send admin notification
            $adminSubject = "New Order Placed";
            smtp_send_mail("inbox@YOURID.mailtrap.io", $adminSubject, $html);
        }
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
