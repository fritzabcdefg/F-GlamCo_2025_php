<?php
session_start();
include('../includes/auth_user.php');
include('../includes/header.php');
include('../includes/config.php');

try {
    mysqli_query($conn, 'START TRANSACTION');

    // Get customer_id based on logged-in user
    $sql = "SELECT customer_id FROM customers WHERE user_id = {$_SESSION['user_id']} LIMIT 1";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $customer_id = $row['customer_id'];

    // Insert into orderinfo
    $q = 'INSERT INTO orderinfo(customer_id, date_placed, date_shipped, shipping) VALUES (?, NOW(), NOW(), ?)';
    $shipping = 10.00;
    $stmt1 = mysqli_prepare($conn, $q);
    mysqli_stmt_bind_param($stmt1, 'id', $customer_id, $shipping);
    mysqli_stmt_execute($stmt1);
    $orderinfo_id = mysqli_insert_id($conn);

    // Prepare reusable statements
    $q2 = 'INSERT INTO orderline(orderinfo_id, item_id, quantity) VALUES (?, ?, ?)';
    $stmt2 = mysqli_prepare($conn, $q2);

    $q3 = 'UPDATE stocks SET quantity = quantity - ? WHERE item_id = ?';
    $stmt3 = mysqli_prepare($conn, $q3);

    // Loop through cart items
    foreach ($_SESSION["cart_products"] as $cart_itm) {
        $product_code = $cart_itm["item_id"];
        $product_qty = $cart_itm["item_qty"];

        // Correct parameter order for orderline
        mysqli_stmt_bind_param($stmt2, 'iii', $orderinfo_id, $product_code, $product_qty);
        mysqli_stmt_execute($stmt2);

        // Update stock quantity
        mysqli_stmt_bind_param($stmt3, 'ii', $product_qty, $product_code);
        mysqli_stmt_execute($stmt3);
    }
    // Commit transaction
    mysqli_commit($conn);
    unset($_SESSION['cart_products']);

    echo "<div class='alert alert-success text-center mt-4'>Checkout successful. Your order has been placed.</div>";

} catch (mysqli_sql_exception $e) {
    echo "<div class='alert alert-danger text-center mt-4'>Error: " . $e->getMessage() . "</div>";
    mysqli_rollback($conn);
}

include('../includes/footer.php');
?>
