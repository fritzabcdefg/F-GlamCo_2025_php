<?php
session_start();
include('../includes/auth_user.php');
include('../includes/header.php');
include('../includes/config.php');

if (!isset($_SESSION["cart_products"]) || count($_SESSION["cart_products"]) === 0) {
    header("Location: view_cart.php");
    exit;
}

$selectedIds = isset($_SESSION['checkout_selected']) ? $_SESSION['checkout_selected'] : [];
$total = 0;
$shipping = 80.00; 

try {
    $conn->begin_transaction();

    foreach ($_SESSION["cart_products"] as $cart_itm) {
        if (in_array($cart_itm["item_id"], $selectedIds)) {
            $subtotal = $cart_itm["item_price"] * $cart_itm["item_qty"];
            $total += $subtotal;
        }
    }

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    $total = 0;
}
?>

<style>
    body {
        margin: 0;
        padding: 0;
        background: #000000ff;
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
        color: #000000ff;
    }
    .checkout-table th {
        background: #F8BBD0;
        color: #000000ff;
    }
    .checkout-actions {
        text-align: right;
        margin-top: 20px;
    }
    .checkout-actions a, .checkout-actions button {
        margin-left: 10px;
        padding: 8px 16px;
        border: none;
        background: #F8BBD0;
        color: #000000ff;
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
    <h1 align="center" style="color:#F69b9A;">Order Summary</h1>
    <form method="POST" action="place_order.php">
        <table class="checkout-table mb-4">
            <thead>
                <tr>
                    <th>Quantity</th>
                    <th>Name</th>
                    <th>Brand</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach ($_SESSION["cart_products"] as $cart_itm): 
                    if (in_array($cart_itm["item_id"], $selectedIds)) {
                        $product_name  = $cart_itm["item_name"];
                        $product_qty   = $cart_itm["item_qty"];
                        $product_price = $cart_itm["item_price"];
                        $product_brand = $cart_itm["supplier_name"];
                        $subtotal      = $product_price * $product_qty;
                ?>
                <tr>
                    <td><?= $product_qty; ?></td>
                    <td><?= htmlspecialchars($product_name); ?></td>
                    <td><?= htmlspecialchars($product_brand); ?></td>
                    <td>₱<?= number_format($product_price, 2); ?></td>
                    <td>₱<?= number_format($subtotal, 2); ?></td>
                </tr>
                <?php 
                    }
                endforeach; 
                ?>
            </tbody>
        </table>

        <table class="checkout-table">
            <tbody>
                <tr>
                    <td style="text-align:right;font-weight:bold;">Shipping Fee:</td>
                    <td style="text-align:center;">₱<?= number_format($shipping, 2); ?></td>
                </tr>
                <tr>
                    <td style="text-align:right;font-weight:bold;">Total Amount:</td>
                    <td style="text-align:center;"><strong>₱<?= number_format($total + $shipping, 2); ?></strong></td>
                </tr>
            </tbody>
        </table>

        <div class="checkout-actions">
            <a href="view_cart.php" class="button">Back to Cart</a>
            <button type="submit" class="confirm">Confirm Checkout</button>
        </div>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
