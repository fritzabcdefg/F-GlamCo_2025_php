<?php
session_start();
include('./includes/header.php');
include('./includes/config.php');

// 📦 Display Products
$sql = "SELECT i.item_id AS itemId, i.name, i.supplier_name, i.sell_price, s.quantity,
               (SELECT filename FROM product_images WHERE item_id = i.item_id ORDER BY created_at ASC LIMIT 1) AS main_image
        FROM items i
        INNER JOIN stocks s USING (item_id)
        WHERE s.quantity > 0
        ORDER BY i.item_id ASC";

$results = mysqli_query($conn, $sql);

if ($results) {
    $products_item = '<ul class="products">';
    while ($row = mysqli_fetch_assoc($results)) {
        // fallback if no image
        $mainImage = !empty($row['main_image']) ? $row['main_image'] : './assets/no-image.png';

        $products_item .= <<<EOT
        <li class="product">
            <form method="POST" action="./cart/cart_update.php">
                <div class="product-content">
                    <div class="product-thumb">
                        <img src="{$mainImage}" width="80" height="80" style="margin-bottom:6px;">
                    </div>
                    <h3>{$row['supplier_name']} - {$row['name']}</h3>
                    <div class="product-info">
                        Price: {$row['sell_price']}
                        <fieldset>
                            <label>
                                <span>Quantity</span>
                                <input type="number" name="item_qty" value="1" min="1" max="{$row['quantity']}"/>
                            </label>
                        </fieldset>
                        <input type="hidden" name="item_id" value="{$row['itemId']}" />
                        <input type="hidden" name="type" value="add" />
                        <div align="center">
                            <a href="./product/show.php?id={$row['itemId']}" class="btn btn-sm btn-outline-primary">View</a>
                            <button type="submit" class="add_to_cart">Add</button>
                        </div>
                    </div>
                </div>
            </form>
        </li>
EOT;
    }
    $products_item .= '</ul>';
    echo $products_item;
}

include('./includes/footer.php');
?>
