<?php
session_start();
include('../includes/config.php');

// 🛒 Add item to cart
if (isset($_POST["type"]) && $_POST["type"] === 'add' && isset($_POST["item_qty"]) && $_POST["item_qty"] > 0) {
    $new_product = [];
    foreach ($_POST as $key => $value) {
        $new_product[$key] = $value;
    }
    unset($new_product['type']);

    $sql = "SELECT i.item_id AS itemId, i.name, i.supplier_name, i.sell_price, s.quantity,
                   (SELECT filename FROM product_images WHERE item_id = i.item_id ORDER BY created_at ASC LIMIT 1) AS main_image
            FROM items i
            INNER JOIN stocks s USING (item_id)
            WHERE i.item_id = {$new_product['item_id']} LIMIT 1";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        $new_product["item_name"]     = $row['name'];
        $new_product["item_price"]    = $row['sell_price'];
        $new_product["item_stock"]    = $row['quantity'];
        $new_product["item_image"]    = $row['main_image'];
        $new_product["supplier_name"] = $row['supplier_name'];
    }

    $_SESSION["cart_products"][$new_product['item_id']] = $new_product;
}

// 🔄 Update quantities, remove items, and handle checkout selection
if (isset($_POST["product_qty"]) || isset($_POST["remove_code"]) || isset($_POST["checkout_select"])) {
    // Update quantities
    if (isset($_POST["product_qty"]) && is_array($_POST["product_qty"])) {
        foreach ($_POST["product_qty"] as $key => $value) {
            if (is_numeric($value) && isset($_SESSION["cart_products"][$key])) {
                $max_stock = $_SESSION["cart_products"][$key]["item_stock"];
                $safe_qty  = max(1, min($value, $max_stock));
                $_SESSION["cart_products"][$key]["item_qty"] = $safe_qty;
            }
        }
    }

    // 🗑️ Remove items
    if (isset($_POST["remove_code"])) {
        $remove_id = intval($_POST["remove_code"]);
        unset($_SESSION["cart_products"][$remove_id]);
    }

    // ✅ Save selected items for checkout
    if (isset($_POST['checkout_select']) && is_array($_POST['checkout_select'])) {
        $_SESSION['checkout_selected'] = array_map('intval', $_POST['checkout_select']);
    } else {
        $_SESSION['checkout_selected'] = [];
    }
}

header('Location: view_cart.php');
exit;
?>
