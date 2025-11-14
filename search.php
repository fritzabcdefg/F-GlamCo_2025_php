<?php
session_start();
include('./includes/header.php');
include('./includes/config.php');

$term = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($term === '') {
    // nothing searched: redirect to home
    header('Location: index.php');
    exit();
}

$like = '%' . $term . '%';
// prepared statement searching item name and category name
$sql = "SELECT i.item_id AS itemId, i.name, i.img_path, i.sell_price, s.quantity, c.name AS category_name
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.category_id
        LEFT JOIN stocks s USING (item_id)
        WHERE (i.name LIKE ? OR c.name LIKE ?) AND (s.quantity IS NULL OR s.quantity > 0)
        ORDER BY i.item_id ASC";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    echo '<div class="container mt-4"><div class="alert alert-danger">Search failed (prepare error).</div></div>';
    include('./includes/footer.php');
    exit();
}

mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$products_item = '<ul class="products">';
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $name   = htmlspecialchars($row['name']);
        $img    = htmlspecialchars($row['img_path']);
        $price  = htmlspecialchars($row['sell_price']);
        $qty    = (int)$row['quantity'];
        $itemId = (int)$row['itemId'];

        $products_item .= <<<EOT
        <li class="product">
            <form method="POST" action="./cart/cart_update.php">
                <div class="product-content">
                    <h3>{$name}</h3>
                    <div class="product-thumb">
                        <img src="./item/{$img}" width="50px" height="50px">
                    </div>
                    <div class="product-info">
                        Price: {$price}
                        <fieldset>
                            <label>
                                <span>Quantity</span>
                                <input type="number" size="2" maxlength="2" name="item_qty" value="1" min="1" max="{$qty}"/>
                            </label>
                        </fieldset>
                        <input type="hidden" name="item_id" value="{$itemId}" />
                        <input type="hidden" name="type" value="add" />
                        <div align="center">
                            <a href="./product/show.php?id={$itemId}" class="btn btn-sm btn-outline-primary">View</a>
                            <button type="submit" class="add_to_cart">Add</button>
                        </div>
                    </div>
                </div>
            </form>
        </li>
EOT;
    }
}
$products_item .= '</ul>';
?>

<div class="container mt-4">
    <h3>Search results for "<?php echo htmlspecialchars($term); ?>"</h3>
    <?php echo $products_item; ?>
</div>

<?php include('./includes/footer.php'); ?>
