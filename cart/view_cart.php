<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');
?>

<!-- Font Awesome for trash icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

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
        color: #000000;
    }
    .cart-table th, .cart-table td {
        padding: 12px;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }
    .cart-table th {
        background: #F8BBD0;
        color: #000000;
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
        color: #000000ff;
        text-decoration: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .cart-actions a.button.checkout {
        background: #F8BBD0;
        color: #000;
    }
    .remove-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: #C71585;
        font-size: 1.2rem;
        transition: color 0.2s ease;
    }
    .remove-btn:hover {
        color: #880E4F;
    }
</style>

<div class="cart-container">
    <h1 align="center" style="color:#F69b9A;">Your Shopping Bag</h1>
    <form method="POST" action="cart_update.php">
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Quantity</th>
                    <th>Name</th>
                    <th>Brand</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Action</th>
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
                        $product_brand = $cart_itm["supplier_name"]; // must be stored in session when adding to cart
                        $subtotal      = $product_price * $product_qty;

                        echo '<tr>';
                        // Select column (checked by default)
                        echo '<td><input type="checkbox" name="checkout_select[]" value="' . $product_code . '" checked /></td>';
                        // Quantity
                        echo '<td><input type="number" min="1" name="product_qty[' . $product_code . ']" value="' . $product_qty . '" style="width:60px;text-align:center;" /></td>';
                        // Name
                        echo '<td>' . htmlspecialchars($product_name) . '</td>';
                        // Brand
                        echo '<td>' . htmlspecialchars($product_brand) . '</td>';
                        // Price
                        echo '<td>₱' . number_format($product_price, 2) . '</td>';
                        // Total
                        echo '<td>₱' . number_format($subtotal, 2) . '</td>';
                        // Action (remove button)
                        echo '<td><button type="submit" name="remove_code" value="' . $product_code . '" class="remove-btn"><i class="fas fa-trash"></i></button></td>';
                        echo '</tr>';

                        $total += $subtotal;
                    }
                }
                ?>
                <tr>
                    <td colspan="7" style="text-align:right;font-weight:bold;">
                        Amount Payable : ₱<?php echo sprintf("%01.2f", $total); ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="cart-actions">
            <a href="../index.php" class="button"> Shop More</a>
            <button type="submit"> Update</button>
            <a href="checkout.php" class="button checkout"> Checkout</a>
        </div>
    </form>
</div>

<?php
include('../includes/footer.php');
?>
