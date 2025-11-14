<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');
print_r($_SESSION);
?>

<h1 align="center">View Cart</h1>
<div class="cart-view-table-back">
    <form method="POST" action="cart_update.php">
        <table width="100%" cellpadding="6" cellspacing="0">
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
                if (isset($_SESSION["cart_products"])) {
                    $total = 0; 
                    $b = 0; 
                    foreach ($_SESSION["cart_products"] as $cart_itm) {
                        $product_name  = $cart_itm["item_name"];
                        $product_qty   = $cart_itm["item_qty"];
                        $product_price = $cart_itm["item_price"];
                        $product_code  = $cart_itm["item_id"];
                        $subtotal      = $product_price * $product_qty;
                        $bg_color      = ($b++ % 2 == 1) ? 'odd' : 'even';

                        echo '<tr class="' . $bg_color . '">';
                        echo '<td><input type="text" size="2" maxlength="2" name="product_qty[' . $product_code . ']" value="' . $product_qty . '" /></td>';
                        echo '<td>' . htmlspecialchars($product_name) . '</td>';
                        echo '<td>' . number_format($product_price, 2) . '</td>';
                        echo '<td>' . number_format($subtotal, 2) . '</td>';
                        echo '<td><input type="checkbox" name="remove_code[]" value="' . $product_code . '" /></td>';
                        echo '</tr>';

                        $total += $subtotal;
                    }
                }
                ?>
                <tr>
                    <td colspan="5" style="text-align:right;">
                        Amount Payable : <?php echo sprintf("%01.2f", $total); ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align:right;">
                        <a href="index.php" class="button">Add More Items</a>
                        <button type="submit">Update</button>
                        <a href="checkout.php" class="button">Checkout</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>

<?php
include('../includes/footer.php');
?>
