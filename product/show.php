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
$sql = "SELECT i.*, s.quantity FROM items i LEFT JOIN stocks s USING (item_id) WHERE i.item_id = {$id} LIMIT 1";
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

// fetch reviews (visible only)
$revQ = mysqli_query($conn, "SELECT * FROM reviews WHERE item_id = {$id} AND is_visible = 1 ORDER BY created_at DESC");
$reviews = [];
if ($revQ) {
    while ($r = mysqli_fetch_assoc($revQ)) $reviews[] = $r;
}

?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <?php if (count($images) > 0): ?>
                <div>
                    <?php foreach ($images as $img): ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" style="max-width:100%;height:auto;margin-bottom:8px;" />
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <?php if (!empty($item['img_path'])): ?>
                    <img src="<?php echo htmlspecialchars($item['img_path']); ?>" style="max-width:100%;height:auto;" />
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <h2><?php echo htmlspecialchars($item['name']); ?></h2>
            <p>Price: <?php echo htmlspecialchars($item['sell_price']); ?></p>
            <p>In stock: <?php echo htmlspecialchars($item['quantity']); ?></p>

            <!-- Add to cart form -->
            <form method="POST" action="../cart/cart_update.php">
                <?php echo csrf_input(); ?>
                <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                <input type="number" name="item_qty" value="1" min="1" max="<?php echo (int)$item['quantity']; ?>">
                <button class="btn btn-primary">Add to cart</button>
            </form>

        </div>
    </div>

    <hr />

    <div class="reviews">
        <h3>Reviews (<?php echo count($reviews); ?>)</h3>
        <?php if (count($reviews) == 0): ?>
            <p>No reviews yet. Be the first to write one!</p>
        <?php else: ?>
            <?php foreach ($reviews as $rev): ?>
                <div style="border-bottom:1px solid #eee;padding:8px 0;">
                    <strong><?php echo htmlspecialchars($rev['user_name'] ?? 'Anonymous'); ?></strong>
                    <?php if (!empty($rev['rating'])): ?> - Rating: <?php echo intval($rev['rating']); ?>/5<?php endif; ?>
                    <div style="margin-top:6px;"><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></div>
                    <div style="font-size:0.8em;color:#777;"><?php echo $rev['created_at']; ?>
                    <?php if (isset($_SESSION['user_id']) && $rev['user_id'] == $_SESSION['user_id']): ?>
                        <a href="reviews/edit.php?id=<?php echo $rev['id']; ?>" style="margin-left:10px;">Edit</a>
                    <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <h4>Write a review</h4>
        <form method="POST" action="reviews/store.php">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
            <div class="mb-3">
                <label>Your name</label>
                <input type="text" name="user_name" class="form-control" value="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label>Rating (1-5)</label>
                <select name="rating" class="form-control">
                    <option value="">--</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Comment</label>
                <textarea name="comment" class="form-control" rows="4"></textarea>
            </div>
            <button class="btn btn-primary">Submit review</button>
        </form>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
