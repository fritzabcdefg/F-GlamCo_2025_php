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

// calculate average rating
$ratingSum = 0;
$ratingCount = 0;
foreach ($reviews as $rev) {
    if (!empty($rev['rating'])) {
        $ratingSum += intval($rev['rating']);
        $ratingCount++;
    }
}
$averageRating = $ratingCount > 0 ? round($ratingSum / $ratingCount, 0) : 0;

// calculate markup price (30%)
$markupPrice = $item['sell_price'] * 1.3;
?>

<style>
    body {
        font-family: 'Helvetica Neue', 'Helvetica World', Arial, sans-serif;
    }
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
    .product-container {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .h2-dark {
        color: #000;
        font-weight: bold;
        font-size: 1.6em;
        margin-bottom: 10px;
    }
    .product-details {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 15px;
    }
    .original-price {
        color: red;
        text-decoration: line-through;
        margin-right: 10px;
        font-weight: bold;
    }
    .real-price {
        color: #000;
        font-weight: bold;
    }
    .star-rating {
        font-size: 1.5em;
        color: #FFD700;
        letter-spacing: 8px;
    }
    .star-rating .empty {
        color: #ccc;
    }
    .review-container {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        color: #000;
    }
    .review-container h5 {
        color: #000;
        font-weight: bold;
        margin-bottom: 15px;
    }
    .btn-add {
        font-size: 1.0em;      
        padding: 10px 20px;    
        width: auto;           
        margin-top: 3px;      
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
            <h2><?php echo htmlspecialchars($item['supplier_name']); ?></h2>
            <div class="h2-dark"><?php echo htmlspecialchars($item['name']); ?></div>

            <div class="product-details"> 
                <p>
                    <span class="original-price">₱<?php echo number_format($markupPrice, 2); ?></span>
                    <span class="real-price">₱<?php echo number_format($item['sell_price'], 2); ?></span>
                </p>

                <div>
                    <span class="star-rating">
                        <?php
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $averageRating) {
                                echo "&#9733;"; // filled star
                            } else {
                                echo "<span class='empty'>&#9733;</span>"; // empty star
                            }
                        }
                        ?>
                    </span>
                    <span>(<?php echo $averageRating; ?>/5 from <?php echo $ratingCount; ?> reviews)</span>
                </div>

                <p>In stock: <?php echo htmlspecialchars($item['quantity']); ?></p>
            </div>

            <!-- Add to cart form -->
            <form method="POST" action="../cart/cart_update.php" class="mt-3">
                <input type="hidden" name="type" value="add">
                <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">

                <div class="d-flex flex-column align-items-start gap-3">
                    <input type="number" 
                        name="item_qty" 
                        value="1" 
                        min="1" 
                        max="<?php echo (int)$item['quantity']; ?>" 
                        class="form-control" 
                        style="width:120px;">

                    <button class="btn btn-primary btn-add" name="submit">Add to Bag</button>
                </div>
            </form>

            <div class="mt-3">
                <h5 style>Description:</h5>
                <p><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
            </div>
        </div>
    </div>

    <hr />

    <!-- ✅ Reviews stay at bottom -->
    <div class="reviews mt-4 review-container">
        <h5 class="d-flex align-items-center gap-2">
            <span>REVIEWS</span>
            <span class="badge bg-dark text-white">
                <?php echo count($reviews); ?>
            </span>
        </h5>

        <?php if (count($reviews) === 0): ?>
            <p class="text-muted">No reviews yet</p>
        <?php else: ?>
            <?php foreach ($reviews as $rev): ?>
                <div class="p-3 mb-2 border rounded" style="background-color:#ffe6f0;">
                    <strong style="color:#000;">
                        <?php echo htmlspecialchars($rev['user_name'] ?? 'Anonymous'); ?>
                    </strong>
                    <?php if (!empty($rev['rating'])): ?>
                        <span class="star-rating ms-2">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= intval($rev['rating'])) {
                                    echo "&#9733;";
                                } else {
                                    echo "<span class='empty'>&#9733;</span>";
                                }
                            }
                            ?>
                        </span>
                    <?php endif; ?>
                    <div class="mt-2 text-dark">
                        <?php echo nl2br(htmlspecialchars($rev['comment'])); ?>
                    </div>
                    <div class="mt-1 text-muted" style="font-size:0.85em;">
                        <?php echo $rev['created_at']; ?>
                        <?php if (isset($_SESSION['user_id']) && $rev['user_id'] == $_SESSION['user_id']): ?>
                            <a href="reviews/edit.php?id=<?php echo $rev['id']; ?>" class="ms-2" style="color:#000;">Edit</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<br>

<?php include('../includes/footer.php'); ?>
