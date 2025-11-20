<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../user/login.php?error=unauthorized");
    exit();
}

// Require admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?error=adminonly");
    exit();
}
$keyword = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

if ($keyword) {
    // Search by item name or supplier name
    $sql = "SELECT i.item_id, i.name, i.supplier_name, i.cost_price, i.sell_price, s.quantity,
                   (SELECT filename FROM product_images WHERE item_id = i.item_id ORDER BY created_at ASC LIMIT 1) AS main_image
            FROM items i
            LEFT JOIN stocks s USING (item_id)
            WHERE i.name LIKE '%{$keyword}%' OR i.supplier_name LIKE '%{$keyword}%'
            ORDER BY i.item_id DESC";
} else {
    $sql = "SELECT i.item_id, i.name, i.supplier_name, i.cost_price, i.sell_price, s.quantity,
                   (SELECT filename FROM product_images WHERE item_id = i.item_id ORDER BY created_at ASC LIMIT 1) AS main_image
            FROM items i
            LEFT JOIN stocks s USING (item_id)
            ORDER BY i.item_id DESC";
}

$result = mysqli_query($conn, $sql);
$itemCount = $result ? mysqli_num_rows($result) : 0;
?>

<div class="container mt-4">
    <h2 class="mb-4" style="color:#ffffff; font-weight:700;">Products</h2>

    <!-- Action bar -->
    <div class="mb-3">
        <a href="create.php" class="btn btn-sm btn-success">Add Item</a>
        <a href="/F&LGlamCo/category/index.php" class="btn btn-sm btn-primary">Add Category</a>
    </div>

    <!-- Item count -->
    <div class="alert alert-info">
        Items on List: <?= $itemCount ?>
    </div>

    <!-- Product Table -->
    <table class="table table-striped" style="border:1px solid #F8BBD0; border-radius:10px; overflow:hidden;">
        <thead>
            <tr>
                <th>Image</th>
                <th>ID</th>
                <th>Supplier - Name</th>
                <th>Selling Price</th>
                <th>Cost Price</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($itemCount > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php 
                        $mainImage = !empty($row['main_image']) ? $row['main_image'] : './assets/no-image.png';
                    ?>
                    <tr>
                        <td>
                            <img src="<?= htmlspecialchars($mainImage) ?>" alt="Product Image" 
                                 style="width:60px;height:60px;object-fit:cover;border-radius:6px;">
                        </td>
                        <td><?= (int)$row['item_id'] ?></td>
                        <td><?= htmlspecialchars($row['supplier_name']) ?> - <?= htmlspecialchars($row['name']) ?></td>
                        <td>₱<?= number_format($row['sell_price'], 2) ?></td>
                        <td>₱<?= number_format($row['cost_price'], 2) ?></td>
                        <td><?= (int)$row['quantity'] ?></td>
                        <td>
                            <!-- Edit -->
                            <a href="edit.php?id=<?= $row['item_id'] ?>" class="btn btn-sm btn-warning">
                                <i class="fa-regular fa-pen-to-square"></i> Edit
                            </a>
                            <!-- Delete -->
                            <a href="delete.php?id=<?= $row['item_id'] ?>" class="btn btn-sm btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this item?');">
                                <i class="fa-solid fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">No products found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include('../includes/footer.php'); ?>
