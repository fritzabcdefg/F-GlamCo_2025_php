<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

$alertMessage = '';

// --- Handle remove action ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_code'])) {
    $removeId = $_POST['remove_code'];
    if (isset($_SESSION["cart_products"])) {
        foreach ($_SESSION["cart_products"] as $key => $cart_itm) {
            if ($cart_itm["item_id"] == $removeId) {
                unset($_SESSION["cart_products"][$key]);
                break;
            }
        }
        // reindex array
        $_SESSION["cart_products"] = array_values($_SESSION["cart_products"]);
    }
}

// --- Handle update quantities ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (isset($_POST['product_qty']) && is_array($_POST['product_qty'])) {
        foreach ($_POST['product_qty'] as $id => $qty) {
            foreach ($_SESSION["cart_products"] as &$cart_itm) {
                if ($cart_itm["item_id"] == $id) {
                    $cart_itm["item_qty"] = max(1, intval($qty));
                }
            }
        }
        unset($cart_itm); // break reference
    }
}

// --- Handle checkout validation ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['go_checkout'])) {
    if (empty($_POST['checkout_select'])) {
        $alertMessage = "⚠️ Please select at least one item before proceeding to checkout.";
    } else {
        $_SESSION['checkout_selected'] = $_POST['checkout_select'];
        header("Location: checkout.php");
        exit;
    }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
    body { margin:0; padding:0; background:#000000ff; }
    .cart-container { width:100%; min-height:100vh; padding:20px; box-sizing:border-box; }
    .cart-table { width:100%; border-collapse:collapse; background:#fff; box-shadow:0 2px 6px rgba(0,0,0,0.1); color:#000; }
    .cart-table th, .cart-table td { padding:12px; text-align:center; border-bottom:1px solid #ddd; }
    .cart-table th { background:#F8BBD0; color:#000; }
    .cart-actions { text-align:right; margin-top:20px; }
    .cart-actions a, .cart-actions button { margin-left:10px; padding:8px 16px; border:none; background:#F8BBD0; color:#000; text-decoration:none; border-radius:4px; cursor:pointer; }
    .cart-actions a.button.checkout { background:#F8BBD0; color:#000; }
    .remove-btn { background:none; border:none; cursor:pointer; color:#C71585; font-size:1.2rem; transition:color 0.2s ease; }
    .remove-btn:hover { color:#880E4F; }
    .alert { color:#fff; background:#C71585; padding:10px; margin-bottom:15px; text-align:center; border-radius:4px; }
</style>

<div class="cart-container">
    <h1 align="center" style="color:#F69b9A;">Your Shopping Bag</h1>

    <?php if (!empty($alertMessage)) echo "<div class='alert'>$alertMessage</div>"; ?>

    <form method="POST" action="view_cart.php">

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
                if (isset($_SESSION["cart_products"]) && count($_SESSION["cart_products"]) > 0) {
                    foreach ($_SESSION["cart_products"] as $cart_itm) {
                        $product_name  = $cart_itm["item_name"];
                        $product_qty   = $cart_itm["item_qty"];
                        $product_price = $cart_itm["item_price"];
                        $product_code  = $cart_itm["item_id"];
                        $product_brand = $cart_itm["supplier_name"];
                        $subtotal      = $product_price * $product_qty;

                        $checked = (isset($_SESSION['checkout_selected']) && in_array($product_code, $_SESSION['checkout_selected'])) ? 'checked' : '';

                        echo '<tr>';
                        echo '<td><input type="checkbox" class="selectItem" name="checkout_select[]" value="' . $product_code . '" ' . $checked . '></td>';
                        echo '<td><input type="number" min="1" name="product_qty[' . $product_code . ']" value="' . $product_qty . '" style="width:60px;text-align:center;"></td>';
                        echo '<td>' . htmlspecialchars($product_name) . '</td>';
                        echo '<td>' . htmlspecialchars($product_brand) . '</td>';
                        echo '<td>₱' . number_format($product_price, 2) . '</td>';
                        echo '<td>₱' . number_format($subtotal, 2) . '</td>';
                        echo '<td><button type="submit" name="remove_code" value="' . $product_code . '" class="remove-btn"><i class="fas fa-trash"></i></button></td>';
                        echo '</tr>';

                        $total += $subtotal;
                    }
                } else {
                    echo '<tr><td colspan="7">Your cart is empty.</td></tr>';
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
            <a href="../index.php" class="button">Shop More</a>
            <button type="submit" name="update_cart" value="1">Update</button>
            <button type="submit" name="go_checkout" value="1" class="button checkout">Checkout</button>
        </div>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
