<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/header.php');
include('../includes/config.php');

try {
    $conn->begin_transaction();
    $sql = "SELECT * FROM categories";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        throw new Exception("Query failed");
    }
    $count = mysqli_num_rows($result);
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    $count = 0;
    $result = false;
}
?>

<div class="container mt-4">
    <a href="create.php" class="btn btn-primary mb-3">Add Category</a>
    <h4>Categories (<?= $count ?>)</h4>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($count > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= (int)$row['category_id']; ?></td>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td><?= htmlspecialchars($row['description']); ?></td>
                        <td>
                            <a href="edit.php?id=<?= (int)$row['category_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="delete.php?id=<?= (int)$row['category_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this category?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">No categories found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include('../includes/footer.php'); ?>
