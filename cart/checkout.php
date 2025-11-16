<?php
session_start();
include('../includes/auth_user.php');
include('../includes/header.php');
include('../includes/config.php');

// If no cart items, redirect back
if (!isset($_SESSION["cart_products"]) || count($_SESSION["cart_products"]) === 0) {
    header("Location: view_cart.php");
    exit;
}

$total = 0;
$shipping = 80.00; // flat shipping fee
?>

<style>
    body {
        margin: 0;
        padding: 0;
        background: #f9f9f9;
    }
    .checkout-container {
        width: 100%;
        min-height: 100vh;
        padding: 20px;
        box-sizing: border-box;
    }
    .checkout-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .checkout-table th, .checkout-table td {
        padding: 12px;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }
    .checkout-table th {
        background: #007bff;
        color: #fff;
    }
    .checkout-actions {
        text-align: right;
        margin-top: 20px;
    }
    .checkout-actions a, .checkout-actions button {
        margin-left: 10px;
        padding: 8px 16px;
        border: none;
        background: #007bff;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .checkout-actions a.button {
        background: #6c757d;
    }
    .checkout-actions button.confirm {
        background: #28a745;
    }
</style>

<div class="checkout-container">
    <h1 align="center">✅ Checkout</h1>
    <form method="POST" action="process_checkout.php">
        <table class="checkout-table">
            <thead>
                <tr>
                    <th>Quantity</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION["cart_products"] as $cart_itm): 
                    $product_name  = $cart_itm["item_name"];
                    $product_qty   = $cart_itm["item_qty"];
                    $product_price = $cart_itm["item_price"];
                    $subtotal      = $product_price * $product_qty;
                    $total += $subtotal;
                ?>
                <tr>
                    <td><?php echo $product_qty; ?></td>
                    <td><?php echo htmlspecialchars($product_name); ?></td>
                    <td>₱<?php echo number_format($product_price, 2); ?></td>
                    <td>₱<?php echo number_format($subtotal, 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" style="text-align:right;font-weight:bold;">Shipping Fee:</td>
                    <td>₱<?php echo number_format($shipping, 2); ?></td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align:right;font-weight:bold;">Total Amount:</td>
                    <td>₱<?php echo number_format($total + $shipping, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="checkout-actions">
            <a href="view_cart.php" class="button">🔄 Back to Cart</a>
            <button type="submit" class="confirm">✅ Confirm Checkout</button>
        </div>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
