<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

// 🛒 Add item to cart
if (isset($_POST["type"]) && $_POST["type"] === 'add' && isset($_POST["item_qty"]) && $_POST["item_qty"] > 0) {
    $new_product = [];
    foreach ($_POST as $key => $value) {
        $new_product[$key] = $value;
    }
    unset($new_product['type']);

    // Fetch product details (with first image from product_images)
    $sql = "SELECT i.item_id AS itemId, i.name, i.sell_price, s.quantity,
                   (SELECT filename FROM product_images WHERE item_id = i.item_id ORDER BY created_at ASC LIMIT 1) AS main_image
            FROM items i
            INNER JOIN stocks s USING (item_id)
            WHERE i.item_id = {$new_product['item_id']} LIMIT 1";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        $new_product["item_name"]  = $row['name'];
        $new_product["item_price"] = $row['sell_price'];
        $new_product["item_stock"] = $row['quantity']; // ✅ Store stock for validation
        $new_product["item_image"] = $row['main_image']; // ✅ Store image for cart display
    }

    // Replace existing item if already in cart
    if (isset($_SESSION["cart_products"][$new_product['item_id']])) {
        unset($_SESSION["cart_products"][$new_product['item_id']]);
    }

    $_SESSION["cart_products"][$new_product['item_id']] = $new_product;
}

// 🔄 Update quantities or remove items
if (isset($_POST["product_qty"]) || isset($_POST["remove_code"])) {
    if (isset($_POST["product_qty"]) && is_array($_POST["product_qty"])) {
        foreach ($_POST["product_qty"] as $key => $value) {
            if (is_numeric($value) && isset($_SESSION["cart_products"][$key])) {
                $max_stock = $_SESSION["cart_products"][$key]["item_stock"];
                $safe_qty = max(1, min($value, $max_stock)); // ✅ Enforce min=1 and max=stock
                $_SESSION["cart_products"][$key]["item_qty"] = $safe_qty;
            }
        }
    }

    // 🗑️ Remove items
    if (isset($_POST["remove_code"]) && is_array($_POST["remove_code"])) {
        foreach ($_POST["remove_code"] as $key) {
            unset($_SESSION["cart_products"][$key]);
        }
    }
}

header('Location: ../index.php');
exit;
