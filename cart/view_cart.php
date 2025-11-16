<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');
?>

<style>
    body {
        margin: 0;
        padding: 0;
        background: #000000ff;
    }
    .cart-container {
        width: 100%;
        min-height: 100vh;
        padding: 20px;
        box-sizing: border-box;
    }
    .cart-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .cart-table th, .cart-table td {
        padding: 12px;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }
    .cart-table th {
        background: #F8BBD0;
        color: #fff;
    }
    .cart-actions {
        text-align: right;
        margin-top: 20px;
    }
    .cart-actions a, .cart-actions button {
        margin-left: 10px;
        padding: 8px 16px;
        border: none;
        background: #F8BBD0;
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .cart-actions a.button {
        background: #F8BBD0;
    }
    .cart-actions a.button.checkout {
        background: #F8BBD0;
        color: #000;
    }
</style>

<div class="cart-container">
    <h1 align="center">Your Shopping Bag</h1>
    <form method="POST" action="cart_update.php">
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Quantity</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Remove</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                if (isset($_SESSION["cart_products"])) {
                    foreach ($_SESSION["cart_products"] as $cart_itm) {
                        $product_name  = $cart_itm["item_name"];
                        $product_qty   = $cart_itm["item_qty"];
                        $product_price = $cart_itm["item_price"];
                        $product_code  = $cart_itm["item_id"];
                        $subtotal      = $product_price * $product_qty;

                        echo '<tr>';
                        echo '<td><input type="number" min="1" name="product_qty[' . $product_code . ']" value="' . $product_qty . '" style="width:60px;text-align:center;" /></td>';
                        echo '<td>' . htmlspecialchars($product_name) . '</td>';
                        echo '<td>₱' . number_format($product_price, 2) . '</td>';
                        echo '<td>₱' . number_format($subtotal, 2) . '</td>';
                        echo '<td><input type="checkbox" name="remove_code[]" value="' . $product_code . '" /></td>';
                        echo '</tr>';

                        $total += $subtotal;
                    }
                }
                ?>
                <tr>
                    <td colspan="5" style="text-align:right;font-weight:bold;">
                        Amount Payable : ₱<?php echo sprintf("%01.2f", $total); ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="cart-actions">
            <a href="index.php" class="button"> Add More Items</a>
            <button type="submit"> Update</button>
            <a href="checkout.php" class="button checkout"> Checkout</a>
        </div>
    </form>
</div>

<?php
include('../includes/footer.php');
?>
