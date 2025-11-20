<?php
session_start();
include('./includes/header.php');
include('./includes/config.php');

$term = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($term === '') {
    header('Location: index.php');
    exit();
}

$like = '%' . $term . '%';

$sql = "SELECT i.item_id AS itemId, i.name, i.supplier_name, i.sell_price, s.quantity,
               (SELECT filename FROM product_images WHERE item_id = i.item_id ORDER BY created_at ASC LIMIT 1) AS main_image
        FROM items i
        LEFT JOIN stocks s USING (item_id)
        WHERE (i.name LIKE ? OR i.supplier_name LIKE ?) AND s.quantity > 0
        ORDER BY i.item_id ASC";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    echo '<div class="container mt-4"><div class="alert alert-danger">Search failed (prepare error).</div></div>';
    include('./includes/footer.php');
    exit();
}

mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
mysqli_stmt_execute($stmt);
$results = mysqli_stmt_get_result($stmt);
?>

<style>
    .products { padding:0; margin:0 auto; max-width:1100px; display:flex; flex-wrap:wrap; justify-content:center; gap:20px; }
    .product { background:#fff; width:500px; min-height:250px; margin:12px; padding:16px; border:1px solid #ccc; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1); display:flex; flex-direction:column; justify-content:space-between; }
    .product-content { display:flex; flex-direction:row; gap:16px; flex-grow:1; }
    .product-thumb { flex:1; display:flex; align-items:center; justify-content:center; }
    .product-thumb img { max-width:100%; max-height:200px; object-fit:contain; border-radius:6px; }
    .product-details { flex:2; display:flex; flex-direction:column; justify-content:flex-start; }
    .product-details h4 { margin:0; font-size:1.1em; color:#C71585; font-weight:600; }
    .product-details h5 { margin:4px 0; font-size:1em; color:#333; }
    .product-details .price { font-size:1em; font-weight:bold; margin:6px 0; color:#444; }
    .product-details fieldset { border:none; padding:0; margin:6px 0; }
    .product-details input { width:80px; padding:4px; text-align:center; }
    .product-actions { margin-top:auto; display:flex; justify-content:flex-start; gap:10px; }
    .view-btn, .add_to_cart { flex:1; text-align:center; padding:10px; border-radius:4px; border:none; cursor:pointer; font-size:0.9em; }
    .view-btn { background:#000; color:#fff; text-decoration:none; }
    .view-btn:hover { background:#F69B9A !important; color:#880E4F !important; }
    .add_to_cart { background:#000; color:#fff; }
    .add_to_cart:hover { background:#333; }
</style>

<div class="container mt-4">
    <h3>Search results for "<?php echo htmlspecialchars($term); ?>"</h3>

    <?php
    if ($results && mysqli_num_rows($results) > 0) {
        echo '<ul class="products">';
        while ($row = mysqli_fetch_assoc($results)) {
            $mainImage = !empty($row['main_image']) ? $row['main_image'] : './assets/no-image.png';
            ?>
            <li class="product">
                <form method="POST" action="./cart/cart_update.php">
                    <div class="product-content">
                        <div class="product-thumb">
                            <img src="<?php echo htmlspecialchars($mainImage); ?>" alt="Product Image">
                        </div>
                        <div class="product-details">
                            <h4><?php echo htmlspecialchars($row['supplier_name']); ?></h4>
                            <h5><?php echo htmlspecialchars($row['name']); ?></h5>
                            <div class="price">₱<?php echo number_format($row['sell_price'], 2); ?></div>
                            <fieldset>
                                <label>
                                    <span>Quantity</span>
                                    <!-- ✅ removed type="number", min, max -->
                                    <input type="text" name="item_qty" value="1"/>
                                </label>
                            </fieldset>
                            <div class="product-actions">
                                <a href="./product/show.php?id=<?php echo $row['itemId']; ?>" class="view-btn">View</a>
                                <button type="submit" class="add_to_cart">Add</button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="item_id" value="<?php echo $row['itemId']; ?>" />
                    <input type="hidden" name="type" value="add" />
                </form>
            </li>
            <?php
        }
        echo '</ul>';
    } else {
        echo "<p>No products found for your search.</p>";
    }
    ?>
</div>

<?php include('./includes/footer.php'); ?>
