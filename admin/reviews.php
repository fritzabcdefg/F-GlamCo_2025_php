<?php
session_start();
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';

// list reviews
$sql = "SELECT r.*, i.name AS item_name 
        FROM reviews r 
        JOIN items i ON r.item_id = i.item_id 
        ORDER BY r.created_at DESC";
$res = mysqli_query($conn, $sql);
$itemCount = $res ? mysqli_num_rows($res) : 0;
?>

<div class="container mt-4">
    <h2 class="mb-4" style="color:#ffffff; font-weight:700;">Product Reviews</h2>

    <!-- Review count -->
    <div class="alert alert-info">
        Total Reviews: <?= $itemCount ?>
    </div>

    <!-- Reviews Table -->
    <table class="table table-striped" style="border:1px solid #F8BBD0; border-radius:10px; overflow:hidden;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>User ID</th>
                <th>User</th>
                <th>Rating</th>
                <th>Comment</th>
                <th>Visible</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($itemCount > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($res)): ?>
                    <tr>
                        <td><?= (int)$row['id']; ?></td>
                        <td><?= htmlspecialchars($row['item_name']); ?></td>
                        <td><?= $row['user_id'] ?? 'N/A'; ?></td>
                        <td><?= htmlspecialchars($row['user_name'] ?? 'Anonymous'); ?></td>
                        <td>
                            <!-- Rating badge -->
                            <span class="badge bg-primary"><?= (int)$row['rating']; ?>/5</span>
                        </td>
                        <td><?= htmlspecialchars($row['comment']); ?></td>
                        <td>
                            <?php if ($row['is_visible']): ?>
                                <span class="badge bg-success">Yes</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">No</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['created_at']); ?></td>
                        <td>
                            <!-- Delete -->
                            <a href="reviews_delete.php?id=<?= (int)$row['id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Delete review?')">
                                <i class="fa-solid fa-trash"></i> Delete
                            </a>
                            <!-- Toggle visibility -->
                            <a href="reviews_toggle.php?id=<?= (int)$row['id']; ?>" 
                               class="btn btn-sm btn-warning ms-1">
                                <i class="fa-regular fa-eye-slash"></i> Toggle Visible
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9">No reviews found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
