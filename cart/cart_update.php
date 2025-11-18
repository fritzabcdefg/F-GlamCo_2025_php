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
            WHERE i.item_id = " . intval($new_product['item_id']) . " LIMIT 1";

    $result = mysqli_query($conn, $sql);
    $row = $result ? mysqli_fetch_assoc($result) : null;

    if ($row) {
        $new_product["item_id"]       = (int)$row['itemId'];
        $new_product["item_name"]     = $row['name'];
        $new_product["item_price"]    = (float)$row['sell_price'];
        $new_product["item_stock"]    = (int)$row['quantity']; // for validation
        $new_product["item_image"]    = $row['main_image'];    // optional for display
        $new_product["supplier_name"] = $row['supplier_name']; // brand
        $new_product["item_qty"]      = isset($new_product["item_qty"]) ? max(1, (int)$new_product["item_qty"]) : 1;
    }

    // Upsert into cart (merge quantities if item already exists)
    if (!isset($_SESSION["cart_products"])) {
        $_SESSION["cart_products"] = [];
    }

    $id = $new_product['item_id'];
    if (isset($_SESSION["cart_products"][$id])) {
        // Merge quantity with existing
        $existing_qty = (int)$_SESSION["cart_products"][$id]["item_qty"];
        $max_stock    = isset($_SESSION["cart_products"][$id]["item_stock"]) ? (int)$_SESSION["cart_products"][$id]["item_stock"] : PHP_INT_MAX;
        $new_qty      = min($existing_qty + $new_product["item_qty"], $max_stock);
        $_SESSION["cart_products"][$id]["item_qty"] = $new_qty;
    } else {
        // Add as new item
        $_SESSION["cart_products"][$id] = $new_product;
    }

    // After add, return to referrer (index) and do not alter checkout selection
    header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.php'));
    exit;
}

// 🔄 Update quantities, remove items, capture checkout selection, and decide redirect
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Update quantities
    if (isset($_POST["product_qty"]) && is_array($_POST["product_qty"]) && isset($_SESSION["cart_products"])) {
        foreach ($_POST["product_qty"] as $key => $value) {
            $key = (int)$key;
            if (isset($_SESSION["cart_products"][$key])) {
                $max_stock = isset($_SESSION["cart_products"][$key]["item_stock"]) ? (int)$_SESSION["cart_products"][$key]["item_stock"] : PHP_INT_MAX;
                $safe_qty  = max(1, min((int)$value, $max_stock));
                $_SESSION["cart_products"][$key]["item_qty"] = $safe_qty;
            }
        }
    }

    // Remove item
    if (isset($_POST["remove_code"])) {
        $remove_id = (int)$_POST["remove_code"];
        if (isset($_SESSION["cart_products"][$remove_id])) {
            unset($_SESSION["cart_products"][$remove_id]);
        }
    }

    // Save selected items
    if (isset($_POST['checkout_select']) && is_array($_POST['checkout_select'])) {
        $_SESSION['checkout_selected'] = array_map('intval', $_POST['checkout_select']);
    }

    // Restrict checkout if none selected
    if (isset($_POST['go_checkout'])) {
        if (empty($_SESSION['checkout_selected'])) {
            echo "<script>alert('Please select an item first.'); window.location.href='view_cart.php';</script>";
            exit;
        } else {
            header('Location: checkout.php');
            exit;
        }
    }
}

// Fallback
header('Location: view_cart.php');
exit;
?>
