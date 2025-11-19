<?php
session_start();
include('../../includes/auth_user.php');
include('../../includes/header.php');
include('../../includes/config.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    echo "<p>Review not found</p>";
    include('../../includes/footer.php');
    exit();
}

// fetch review and check ownership
$sql = "SELECT r.*, i.name AS item_name,
               (SELECT pi.filename FROM product_images pi WHERE pi.item_id = i.item_id LIMIT 1) AS filename
        FROM reviews r 
        JOIN items i ON r.item_id = i.item_id 
        WHERE r.id = {$id} LIMIT 1";
$res = mysqli_query($conn, $sql);
if (!$res || mysqli_num_rows($res) == 0) {
    echo "<p>Review not found</p>";
    include('../../includes/footer.php');
    exit();
}
$review = mysqli_fetch_assoc($res);

// check ownership
if ($review['user_id'] != $_SESSION['user_id']) {
    echo "<p>You can only edit your own reviews.</p>";
    include('../../includes/footer.php');
    exit();
}
?>

<style>
    body {
        background: #000000ff;
        color: #000;
        font-family: "Helvetica World", "Helvetica Neue", Helvetica, Arial, sans-serif;
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
    .btn-cancel {
        background: #6c757d;
        color: #fff;
        border: none;
        padding: 10px 20px;
        margin-left: 10px;
        cursor: pointer;
    }
    .btn-cancel:hover {
        background: #555;
    }
</style>

<div class="review-container">
    <h2>Edit Review for <?php echo htmlspecialchars($review['item_name']); ?></h2>
    <p>You can update your rating and comment below.</p>

    <div class="review-card">
        <div>
            <?php if (!empty($review['filename'])): ?>
                <img src="<?php echo htmlspecialchars($review['filename']); ?>" alt="Product Image">
            <?php else: ?>
                <img src="../../assets/no-image.png" alt="No Image">
            <?php endif; ?>
        </div>
        <div class="review-form">
            <form method="POST" action="update.php">
                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                <input type="hidden" name="item_id" value="<?php echo $review['item_id']; ?>">
                <input type="hidden" name="orderinfo_id" value="<?php echo $review['orderinfo_id']; ?>">

                <div class="mb-3">
                    <label>Your name</label>
                    <input type="text" name="user_name" class="form-control" 
                           value="<?php echo htmlspecialchars($review['user_name'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label>Rating (1-5)</label>
                    <select name="rating" class="form-control" required>
                        <option value="">--</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?php echo $i; ?>" 
                                <?php echo ($review['rating'] == $i) ? 'selected' : ''; ?>>
                                <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Comment</label>
                    <textarea name="comment" class="form-control" rows="4" required><?php 
                        echo htmlspecialchars($review['comment']); 
                    ?></textarea>
                </div>

                <button class="btn-submit">Update Review</button>
                <a href="../../product/show.php?id=<?php echo $review['item_id']; ?>" class="btn-cancel">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
