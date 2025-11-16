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
?>

<style>
    ul.products {
        padding: 0;
        margin: 0 auto;
        max-width: 960px; /* wider row */
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }

    ul.products li {
        background-color: #fff;          /* white container */
        width: 240px;                    /* fixed width */
        height: 360px;                   /* fixed height */
        margin: 12px;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;          /* stack content vertically */
        justify-content: space-between;  /* spread content evenly */
        text-align: center;
    }

    ul.products li h3 {
        margin: 8px 0;
        font-size: 1em;
        color: #333;
        background: none;                /* remove pink header bar */
    }

    .product-thumb img {
        max-width: 100%;
        max-height: 120px;               /* keep image contained */
        object-fit: contain;             /* scale without distortion */
        margin-bottom: 8px;
    }

    .product-info {
        font-size: 0.9em;
        color: #444;
    }

    .add_to_cart {
        background: #000;
        color: #fff;
        border: none;
        padding: 8px 14px;
        cursor: pointer;
        border-radius: 4px;
        margin-top: 8px;
    }

    .add_to_cart:hover {
        background: #333;
    }

</style>

<?php
if ($results) {
    echo '<ul class="products">';
    while ($row = mysqli_fetch_assoc($results)) {
        $mainImage = !empty($row['main_image']) ? $row['main_image'] : './assets/no-image.png';
        ?>
        <li class="product">
            <form method="POST" action="./cart/cart_update.php">
                <div class="product-content">
                    <div class="product-thumb">
                        <img src="<?php echo htmlspecialchars($mainImage); ?>" width="80" height="80" style="margin-bottom:6px;">
                    </div>
                    <h3><?php echo htmlspecialchars($row['supplier_name'] . ' - ' . $row['name']); ?></h3>
                    <div class="product-info">
                        Price: ₱<?php echo number_format($row['sell_price'], 2); ?>
                        <fieldset>
                            <label>
                                <span>Quantity</span>
                                <input type="number" name="item_qty" value="1" min="1" max="<?php echo $row['quantity']; ?>"/>
                            </label>
                        </fieldset>
                        <input type="hidden" name="item_id" value="<?php echo $row['itemId']; ?>" />
                        <input type="hidden" name="type" value="add" />
                        <div align="center">
                            <a href="./product/show.php?id=<?php echo $row['itemId']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                            <button type="submit" class="add_to_cart">Add</button>
                        </div>
                    </div>
                </div>
            </form>
        </li>
        <?php
    }
    echo '</ul>';
}
include('./includes/footer.php');
?>
