<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/header.php');
include('../includes/config.php');

// list reviews
$sql = "SELECT r.*, i.name AS item_name 
        FROM reviews r 
        JOIN items i ON r.item_id = i.item_id 
        ORDER BY r.created_at DESC";
$res = mysqli_query($conn, $sql);
?>

<div class="container mt-4">
    <h3>Product Reviews</h3>
    <table class="table table-striped">
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
            <?php while ($row = mysqli_fetch_assoc($res)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                    <td><?php echo $row['user_id'] ?? 'N/A'; ?></td>
                    <td><?php echo htmlspecialchars($row['user_name'] ?? 'Anonymous'); ?></td>
                    <td><?php echo $row['rating']; ?></td>
                    <td><?php echo htmlspecialchars($row['comment']); ?></td>
                    <td><?php echo $row['is_visible'] ? 'Yes' : 'No'; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td>
                        <a href="reviews_delete.php?id=<?php echo $row['id']; ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Delete review?')">Delete</a>
                        <a href="reviews_toggle.php?id=<?php echo $row['id']; ?>" 
                           class="btn btn-sm btn-secondary">Toggle Visible</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include('../includes/footer.php'); ?>
