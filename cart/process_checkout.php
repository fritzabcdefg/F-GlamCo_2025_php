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

    // Get customer_id and email
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

    // Insert into orderinfo
    $shipping = 10.00;
    $stmt1 = mysqli_prepare($conn, 'INSERT INTO orderinfo(customer_id, date_placed, date_shipped, shipping) VALUES (?, NOW(), NOW(), ?)');
    mysqli_stmt_bind_param($stmt1, 'id', $customer_id, $shipping);
    mysqli_stmt_execute($stmt1);
    $orderinfo_id = mysqli_insert_id($conn);

    // Prepare reusable statements
    $stmt2 = mysqli_prepare($conn, 'INSERT INTO orderline(orderinfo_id, item_id, quantity) VALUES (?, ?, ?)');
    $stmt3 = mysqli_prepare($conn, 'UPDATE stocks SET quantity = quantity - ? WHERE item_id = ?');

    // ✅ Build email BEFORE clearing cart
    $total = 0;
    $html_body = "<h2>Order Confirmation - F&L Glam Co</h2>";
    $html_body .= "<p>Thank you for your order! Here are your items:</p>";
    $html_body .= "<table border='1' cellpadding='6' cellspacing='0' style='border-collapse:collapse;width:100%;'>";
    $html_body .= "<tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>";

    foreach ($_SESSION["cart_products"] as $cart_itm) {
        $product_code = $cart_itm["item_id"];
        $product_qty  = $cart_itm["item_qty"];
        $product_name = $cart_itm["item_name"];
        $product_price = $cart_itm["item_price"];
        $subtotal = $product_price * $product_qty;
        $total += $subtotal;

        // Insert into orderline
        mysqli_stmt_bind_param($stmt2, 'iii', $orderinfo_id, $product_code, $product_qty);
        mysqli_stmt_execute($stmt2);

        // Update stock
        mysqli_stmt_bind_param($stmt3, 'ii', $product_qty, $product_code);
        mysqli_stmt_execute($stmt3);

        // Add to email body
        $html_body .= "<tr>
            <td>{$product_name}</td>
            <td>{$product_qty}</td>
            <td>₱" . number_format($product_price, 2) . "</td>
            <td>₱" . number_format($subtotal, 2) . "</td>
        </tr>";
    }

    $grand_total = $total + $shipping;
    $html_body .= "<tr><td colspan='3' align='right'><strong>Shipping</strong></td><td>₱" . number_format($shipping, 2) . "</td></tr>";
    $html_body .= "<tr><td colspan='3' align='right'><strong>Grand Total</strong></td><td><strong>₱" . number_format($grand_total, 2) . "</strong></td></tr>";
    $html_body .= "</table>";

    // ✅ Commit transaction
    mysqli_commit($conn);

    // ✅ Send emails
    smtp_send_mail("test@mailtrap.io", "New Order Placed", $html_body);
    if ($customer_email) {
        smtp_send_mail($customer_email, "Your Order Confirmation", $html_body);
    }

    // ✅ Clear cart
    unset($_SESSION['cart_products']);

    // ✅ Redirect to thank you page
    header('Location: ../thank_you.php');
    exit;

} catch (mysqli_sql_exception $e) {
    echo "<div class='alert alert-danger text-center mt-4'>Error: " . $e->getMessage() . "</div>";
    mysqli_rollback($conn);
}

include('../includes/footer.php');
?>
