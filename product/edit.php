<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/header.php');
include('../includes/config.php');
require_once __DIR__ . '/../includes/csrf.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$item = null;
if ($id) {
    $sql = "SELECT i.*, s.quantity FROM items i LEFT JOIN stocks s USING (item_id) WHERE i.item_id = {$id} LIMIT 1";
    $res = mysqli_query($conn, $sql);
    if ($res && mysqli_num_rows($res) > 0) {
        $item = mysqli_fetch_assoc($res);
    }
}
// fetch categories for dropdown
$categories = [];
$catRes = mysqli_query($conn, "SELECT category_id, name FROM categories ORDER BY name ASC");
if ($catRes) {
    while ($c = mysqli_fetch_assoc($catRes)) {
        $categories[] = $c;
    }
}
?>

<div class="container mt-4">
    <h3><?php echo $item ? 'Edit Item' : 'Item not found'; ?></h3>
    <?php if (!$item): ?>
        <p>Item not found. <a href="index.php">Back to list</a></p>
    <?php else: ?>
        <form method="POST" action="update.php" enctype="multipart/form-data">
            <?php echo csrf_input(); ?>
            <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($item['name']); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-control">
                    <option value="">-- Select category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>" <?php if(isset($item['category_id']) && $item['category_id'] == $cat['category_id']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Cost Price</label>
                <input type="text" name="cost_price" class="form-control" value="<?php echo htmlspecialchars($item['cost_price']); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Sell Price</label>
                <input type="text" name="sell_price" class="form-control" value="<?php echo htmlspecialchars($item['sell_price']); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" value="<?php echo htmlspecialchars($item['quantity']); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Supplier</label>
                <input type="text" name="supplier_name" class="form-control" value="<?php echo htmlspecialchars($item['supplier_name']); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Current Images</label><br />
                <?php
                // show images from product_images table
                $imgRes = mysqli_query($conn, "SELECT * FROM product_images WHERE item_id = {$item['item_id']} ORDER BY created_at ASC");
                if ($imgRes && mysqli_num_rows($imgRes) > 0):
                    while ($imgRow = mysqli_fetch_assoc($imgRes)):
                ?>
                    <div style="display:inline-block;margin-right:8px;text-align:center;">
                        <img src="<?php echo htmlspecialchars($imgRow['filename']); ?>" alt="" style="width:120px;height:120px;object-fit:cover;border:1px solid #ddd;margin-bottom:4px;" />
                        <div>
                            <label style="font-size:0.85em;"><input type="checkbox" name="delete_images[]" value="<?php echo $imgRow['id']; ?>"> Remove</label>
                        </div>
                    </div>
                <?php
                    endwhile;
                else:
                    if (!empty($item['img_path'])):
                ?>
                    <img src="<?php echo htmlspecialchars($item['img_path']); ?>" alt="" style="max-width:200px;max-height:200px;object-fit:cover;" class="mb-2" />
                <?php
                    endif;
                endif;
                ?>

                <label class="form-label">Add Images</label>
                <input type="file" name="img_paths[]" class="form-control" multiple accept="image/*">
                <small class="form-text text-muted">Select one or more images to add to the gallery.</small>
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Save</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>
