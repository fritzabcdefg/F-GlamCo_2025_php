<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

$keyword = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

if ($keyword) {
    // Search by description or supplier name
    $sql = "SELECT i.item_id, i.name, i.supplier_name, i.cost_price, i.sell_price, s.quantity,
                   (SELECT filename FROM product_images WHERE item_id = i.item_id ORDER BY created_at ASC LIMIT 1) AS main_image
            FROM items i
            LEFT JOIN stocks s USING (item_id)
            WHERE i.name LIKE '%{$keyword}%' OR i.supplier_name LIKE '%{$keyword}%'";
} else {
    $sql = "SELECT i.item_id, i.name, i.supplier_name, i.cost_price, i.sell_price, s.quantity,
                   (SELECT filename FROM product_images WHERE item_id = i.item_id ORDER BY created_at ASC LIMIT 1) AS main_image
            FROM items i
            LEFT JOIN stocks s USING (item_id)";
}

$result = mysqli_query($conn, $sql);
$itemCount = mysqli_num_rows($result);
?>

<div class="container mt-4">
  <div class="action-bar">
    <a href="create.php" class="action-button"> Add Item</a>
    <a href="/F&LGlamCo/category/index.php" class="action-button"> Add Category</a>
  </div>

  <div class="alert">
    Items on List: <?= $itemCount ?>
  </div>

  <!-- Item Table -->
  <table class="item-table">
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
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <?php 
          $mainImage = !empty($row['main_image']) ? $row['main_image'] : './assets/no-image.png';
        ?>
        <tr>
          <td><img src="<?= htmlspecialchars($mainImage) ?>" class="item-thumb" alt="Product Image" /></td>
          <td><?= $row['item_id'] ?></td>
          <td><?= htmlspecialchars($row['supplier_name']) ?> - <?= htmlspecialchars($row['name']) ?></td>
          <td>₱<?= number_format($row['sell_price'], 2) ?></td>
          <td>₱<?= number_format($row['cost_price'], 2) ?></td>
          <td><?= $row['quantity'] ?></td>
          <td>
            <a href="edit.php?id=<?= $row['item_id'] ?>" class="icon-button edit-icon">
              <i class="fa-regular fa-pen-to-square"></i>
            </a>
            <a href="delete.php?id=<?= $row['item_id'] ?>" class="icon-button delete-icon">
              <i class="fa-solid fa-trash"></i>
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include('../includes/footer.php'); ?>
