<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/header.php');
include('../includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../user/login.php?error=unauthorized");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?error=adminonly");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$item = null;
$categories = [];

try {
    $conn->begin_transaction();

    if ($id) {
        $sql = "SELECT i.*, s.quantity 
                FROM items i 
                LEFT JOIN stocks s USING (item_id) 
                WHERE i.item_id = {$id} LIMIT 1";
        $res = mysqli_query($conn, $sql);
        if ($res && mysqli_num_rows($res) > 0) {
            $item = mysqli_fetch_assoc($res);
        }
    }

    $catRes = mysqli_query($conn, "SELECT category_id, name FROM categories ORDER BY name ASC");
    if ($catRes) {
        while ($c = mysqli_fetch_assoc($catRes)) {
            $categories[] = $c;
        }
    }

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
}
?>

<div class="container mt-4">
    <h3><?= $item ? 'Edit Item' : 'Item not found'; ?></h3>
    <?php if (!$item): ?>
        <p>Item not found. <a href="index.php">Back to list</a></p>
    <?php else: ?>
        <form method="POST" action="update.php" enctype="multipart/form-data">
            <input type="hidden" name="item_id" value="<?= $item['item_id']; ?>">
            
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" 
                       value="<?= htmlspecialchars($item['name']); ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4" 
                          placeholder="Enter item description"><?= htmlspecialchars($item['description']); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-control">
                    <option value="">-- Select category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['category_id']; ?>" 
                            <?php if (isset($item['category_id']) && $item['category_id'] == $cat['category_id']) echo 'selected'; ?>>
                            <?= htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Cost Price</label>
                <input type="text" name="cost_price" class="form-control"
                       value="<?= htmlspecialchars($item['cost_price']); ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Sell Price</label>
                <input type="text" name="sell_price" class="form-control"
                       value="<?= htmlspecialchars($item['sell_price']); ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Quantity</label>
                <input type="text" name="quantity" class="form-control"
                       value="<?= htmlspecialchars($item['quantity']); ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Supplier</label>
                <input type="text" name="supplier_name" class="form-control" 
                       value="<?= htmlspecialchars($item['supplier_name']); ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Current Images</label><br />
                <?php
                $imgRes = mysqli_query($conn, "SELECT * FROM product_images WHERE item_id = {$item['item_id']} ORDER BY created_at ASC");
                if ($imgRes && mysqli_num_rows($imgRes) > 0):
                    while ($imgRow = mysqli_fetch_assoc($imgRes)):
                ?>
                        <div style="display:inline-block;margin-right:8px;text-align:center;">
                            <img src="<?= htmlspecialchars($imgRow['filename']); ?>" alt="" 
                                 style="width:120px;height:120px;object-fit:cover;border:1px solid #ddd;margin-bottom:4px;" />
                            <div style="margin-bottom:6px;">
                                <label style="font-size:0.85em; color:#ffffff; display:flex; align-items:center; gap:6px;">
                                    <input type="checkbox" name="delete_images[]" value="<?= $imgRow['id']; ?>"> Remove
                                </label>
                            </div>
                        </div>
                <?php
                    endwhile;
                else:
                    if (!empty($item['img_path'])):
                ?>
                        <img src="<?= htmlspecialchars($item['img_path']); ?>" alt="" 
                             style="max-width:200px;max-height:200px;object-fit:cover;" class="mb-2" />
                <?php
                    endif;
                endif;
                ?>

                <label class="form-label">Add Images</label>
                <input type="file" name="img_paths[]" class="form-control" multiple>
                <small class="form-text text-muted">Select one or more images to add to the gallery.</small>
            </div>
            
            <button type="submit" name="submit" class="btn btn-primary">Save</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>
