<?php
session_start();
include('../../includes/auth_user.php');
include('../../includes/header.php');
include('../../includes/config.php');
require_once __DIR__ . '/../../includes/csrf.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    echo "<p>Review not found</p>";
    include('../../includes/footer.php');
    exit();
}

// fetch review and check ownership
$sql = "SELECT r.*, i.name AS item_name FROM reviews r JOIN items i ON r.item_id = i.item_id WHERE r.id = {$id} LIMIT 1";
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

<div class="container mt-4">
    <h2>Edit Review for <?php echo htmlspecialchars($review['item_name']); ?></h2>

    <form method="POST" action="update.php">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
        <input type="hidden" name="item_id" value="<?php echo $review['item_id']; ?>">

        <div class="mb-3">
            <label>Your name</label>
            <input type="text" name="user_name" class="form-control" value="<?php echo htmlspecialchars($review['user_name'] ?? ''); ?>">
        </div>
        <div class="mb-3">
            <label>Rating (1-5)</label>
            <select name="rating" class="form-control">
                <option value="">--</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo ($review['rating'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Comment</label>
            <textarea name="comment" class="form-control" rows="4"><?php echo htmlspecialchars($review['comment']); ?></textarea>
        </div>
        <button class="btn btn-primary">Update review</button>
        <a href="../../product/show.php?id=<?php echo $review['item_id']; ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include('../../includes/footer.php'); ?>
