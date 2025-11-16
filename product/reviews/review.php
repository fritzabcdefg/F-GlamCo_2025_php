<?php
session_start();
include('../../includes/auth_user.php');
include('../../includes/header.php');
include('../../includes/config.php');

$orderId = isset($_GET['order']) ? intval($_GET['order']) : 0;
$index   = isset($_GET['index']) ? intval($_GET['index']) : 0;

if (!$orderId) {
    echo "<p>Invalid order.</p>";
    include('../../includes/footer.php');
    exit();
}

// Fetch all items in the order
$sql = "SELECT i.item_id, i.name,
               (SELECT pi.filename 
                FROM product_images pi 
                WHERE pi.item_id = i.item_id 
                LIMIT 1) AS filename
        FROM orderline ol
        JOIN items i ON ol.item_id = i.item_id
        WHERE ol.orderinfo_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $orderId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}

if (empty($items)) {
    echo "<p>No items found for this order.</p>";
    include('../../includes/footer.php');
    exit();
}

if (!isset($items[$index])) {
    echo "<div class='container mt-4'><h3>✅ All products in this order have been reviewed!</h3></div>";
    include('../../includes/footer.php');
    exit();
}

$item = $items[$index];
$totalItems = count($items);
$currentItem = $index + 1;
?>

<style>
    body {
        background: #fff;
        color: #000;
        font-family: Arial, sans-serif;
    }
    .review-container {
        max-width: 700px;
        margin: 40px auto;
        padding: 20px;
        border: 1px solid #000;
        background: #fff;
    }
    .review-container h2 {
        text-align: center;
        margin-bottom: 10px;
    }
    .review-container p {
        text-align: center;
        color: #555;
    }
    .review-card {
        display: flex;
        border-top: 1px solid #000;
        padding-top: 20px;
        margin-top: 20px;
    }
    .review-card img {
        max-width: 200px;
        border: 1px solid #000;
    }
    .review-form {
        flex: 1;
        padding-left: 20px;
    }
    .form-control {
        border: 1px solid #000;
        background: #fff;
        color: #000;
    }
    .btn-submit {
        background: #000;
        color: #fff;
        border: none;
        padding: 10px 20px;
        text-transform: uppercase;
        cursor: pointer;
    }
    .btn-submit:hover {
        background: #333;
    }
</style>

<div class="review-container">
    <h2>Write a Review for Order #<?php echo $orderId; ?></h2>
    <p>Reviewing item <?php echo $currentItem; ?> of <?php echo $totalItems; ?></p>

    <div class="review-card">
        <div>
            <?php if (!empty($item['filename'])): ?>
                <img src="<?php echo htmlspecialchars($item['filename']); ?>" alt="Product Image">
            <?php else: ?>
                <img src="../../assets/no-image.png" alt="No Image">
            <?php endif; ?>
        </div>
        <div class="review-form">
            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
            <form method="POST" action="store.php">
                <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                <input type="hidden" name="orderinfo_id" value="<?php echo $orderId; ?>">
                <input type="hidden" name="next_index" value="<?php echo $index + 1; ?>">

                <div class="mb-3">
                    <label>Rating (1-5)</label>
                    <select name="rating" class="form-control" required>
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
                    <textarea name="comment" class="form-control" rows="4" required></textarea>
                </div>

                <button class="btn-submit">Submit Review</button>
            </form>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
