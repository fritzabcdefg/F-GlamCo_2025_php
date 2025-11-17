<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    echo "<p>Product not found</p>";
    include('../includes/footer.php');
    exit();
}

// fetch item
$sql = "SELECT i.*, s.quantity 
        FROM items i 
        LEFT JOIN stocks s USING (item_id) 
        WHERE i.item_id = {$id} LIMIT 1";
$res = mysqli_query($conn, $sql);
if (!$res || mysqli_num_rows($res) == 0) {
    echo "<p>Product not found</p>";
    include('../includes/footer.php');
    exit();
}
$item = mysqli_fetch_assoc($res);

// fetch gallery images
$imgRes = mysqli_query($conn, "SELECT filename FROM product_images WHERE item_id = {$id} ORDER BY created_at ASC");
$images = [];
if ($imgRes) {
    while ($r = mysqli_fetch_assoc($imgRes)) $images[] = $r['filename'];
}

// fetch reviews
$revQ = mysqli_query($conn, "SELECT * FROM reviews WHERE item_id = {$id} AND is_visible = 1 ORDER BY created_at DESC");
$reviews = [];
if ($revQ) {
    while ($r = mysqli_fetch_assoc($revQ)) $reviews[] = $r;
}
?>

<style>
    .image-slideshow {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding: 10px 0;
    }
    .image-slideshow img {
        max-height: 400px;
        border: 1px solid #ddd;
        background: #fff;
        flex-shrink: 0;
    }
      .text-pink { color: #e83e8c; } /* Bootstrap pink */
    .bg-pink { background-color: #e83e8c !important; }

    /* ✅ White background containers */
    .product-container {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .review-container {
        background-color: #000000ff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }
    .h2-dark{
        color: #000000;
        font-weight: bold;
        font-size: 1.5em;
    }

    .product-details p{
        color: #000000;
        font-size: 1.2em;
    }
</style>

<div class="container mt-4 product-container">
    <div class="row">
        <div class="col-md-6">
            <?php if (count($images) > 0): ?>
                <div class="image-slideshow">
                    <?php foreach ($images as $img): ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" alt="Product Image">
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <img src="../assets/no-image.png" class="d-block w-100" style="max-height:400px;object-fit:contain;">
            <?php endif; ?>
        </div>

        <div class="col-md-6 text-dark">
            <h2><?php echo htmlspecialchars($item['supplier_name']); ?> </h2>
            <div class ="h2-dark"><?php echo htmlspecialchars($item['name']); ?></h2> </div> <br>
            <div class = "product-details p"> 
            <p>Price: ₱<?php echo number_format($item['sell_price'], 2); ?></p>
            <p>Cost Price: ₱<?php echo number_format($item['cost_price'], 2); ?></p>
            <p>In stock: <?php echo htmlspecialchars($item['quantity']); ?></p>
            </div>

            <!-- Add to cart form -->
            <form method="POST" action="../cart/cart_update.php">
                <input type="hidden" name="type" value="add">
                <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                <input type="number" name="item_qty" value="1" min="1" max="<?php echo (int)$item['quantity']; ?>">
                <button class="btn btn-primary">Add to Bag</button>
            </form>
        </div>
    </div>

    <hr />

    <!-- Reviews Section -->
    <div class="reviews mt-4 review-container">
        <?php
        $ratingSum = 0;
        $ratingCount = 0;
        foreach ($reviews as $rev) {
            if (!empty($rev['rating'])) {
                $ratingSum += intval($rev['rating']);
                $ratingCount++;
            }
        }
        $averageRating = $ratingCount > 0 ? round($ratingSum / $ratingCount, 2) : 0;
        ?>

        <!-- Ratings -->
        <h5 class="text-pink fw-bold">
            RATINGS - <span class="badge bg-pink text-white"><?php echo $averageRating; ?>/5</span>
        </h5>

        <!-- Reviews -->
        <h5 class="text-pink fw-bold">
            REVIEWS <span class="badge bg-pink text-white">(<?php echo count($reviews); ?>)</span>
        </h5>

        <?php if (count($reviews) === 0): ?>
            <p class="text-muted">No reviews yet</p>
        <?php else: ?>
            <?php foreach ($reviews as $rev): ?>
                <div class="p-3 mb-2 border rounded" style="background-color:#ffe6f0;">
                    <strong class="text-pink">
                        <?php echo htmlspecialchars($rev['user_name'] ?? 'Anonymous'); ?>
                    </strong>
                    <?php if (!empty($rev['rating'])): ?>
                        <span class="badge bg-pink text-white ms-2">Rating: <?php echo intval($rev['rating']); ?>/5</span>
                    <?php endif; ?>
                    <div class="mt-2 text-dark">
                        <?php echo nl2br(htmlspecialchars($rev['comment'])); ?>
                    </div>
                    <div class="mt-1 text-muted" style="font-size:0.85em;">
                        <?php echo $rev['created_at']; ?>
                        <?php if (isset($_SESSION['user_id']) && $rev['user_id'] == $_SESSION['user_id']): ?>
                            <a href="reviews/edit.php?id=<?php echo $rev['id']; ?>" class="ms-2 text-pink">Edit</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>

