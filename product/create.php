<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/header.php');
include('../includes/config.php');

// Fetch categories
$categories = [];
$catRes = mysqli_query($conn, "SELECT category_id, name FROM categories ORDER BY name ASC");
if ($catRes) {
    while ($c = mysqli_fetch_assoc($catRes)) {
        $categories[] = $c;
    }
}
?>

<div class="container mt-4">
    <h3>Create New Item</h3>
    <form method="POST" action="store.php" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="4" placeholder="Enter item description"></textarea>
        </div>

        <div class="mb-3">
            <label>Category</label>
            <select name="category_id" class="form-control" required>
                <option value="">-- Select category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['category_id']; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Cost Price</label>
            <input type="number" name="cost_price" class="form-control" step="0.01" required>
        </div>

        <div class="mb-3">
            <label>Selling Price</label>
            <input type="number" name="sell_price" class="form-control" step="0.01" required>
        </div>

        <div class="mb-3">
            <label>Quantity</label>
            <input type="number" name="quantity" class="form-control" step="1" min="0" required>
        </div>

        <div class="mb-3">
            <label>Supplier</label>
            <input type="text" name="supplier_name" class="form-control">
        </div>

        <div class="mb-3">
            <label>Item Images</label>
            <input type="file" name="img_paths[]" class="form-control" multiple accept="image/*">
        </div>

        <button type="submit" name="submit" class="btn btn-primary">Save</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
