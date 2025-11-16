<?php
session_start();
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';

// Fetch users from database
$sql = "SELECT id, email, role, created_at, active FROM users ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
$itemCount = $result ? mysqli_num_rows($result) : 0;
?>

<div class="container mt-4">
    <h2 class="mb-4" style="color:#ffffff; font-weight:700;">Users</h2>

    <div class="alert alert-info">
        Users on list: <?= $itemCount ?>
    </div>

    <table class="table table-striped" style="border:1px solid #F8BBD0; border-radius:10px; overflow:hidden;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Role</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($itemCount > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo (int)$row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                        <td>
                            <?php if ($row['active']): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- Toggle Active/Inactive -->
                            <form action="toggle_user.php" method="POST" style="display:inline-block;">
                                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                <button type="submit" class="btn btn-sm <?php echo $row['active'] ? 'btn-warning' : 'btn-success'; ?>">
                                    <?php echo $row['active'] ? 'Deactivate' : 'Activate'; ?>
                                </button>
                            </form>

                            <!-- Change Role -->
                            <form action="change_role.php" method="POST" style="display:inline-block; margin-left:8px;">
                                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                <select name="role" class="form-select form-select-sm" style="display:inline-block; width:auto; vertical-align:middle;">
                                    <option value="customer"<?php echo $row['role'] === 'customer' ? ' selected' : ''; ?>>Customer</option>
                                    <option value="admin"<?php echo $row['role'] === 'admin' ? ' selected' : ''; ?>>Admin</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-secondary">Change</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">No users found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
