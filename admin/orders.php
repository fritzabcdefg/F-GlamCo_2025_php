<?php
session_start();
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';

// Query orders from salesPerOrder view
$sql = "SELECT orderinfo_id, total, status FROM salesperorder ORDER BY total DESC";
$result = mysqli_query($conn, $sql);
$itemCount = $result ? mysqli_num_rows($result) : 0;
?>

<div class="container mt-4">
    <h2>Orders</h2>
    <p>Total Orders: <?= $itemCount ?></p>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Total</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($itemCount > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo (int)$row['orderinfo_id']; ?></td>
                        <td>₱<?php echo number_format($row['total'], 2); ?></td>
                        <td>
                            <?php if ($row['status'] === 'Delivered'): ?>
                                <span class="badge bg-success">Delivered</span>
                            <?php elseif ($row['status'] === 'Cancelled'): ?>
                                <span class="badge bg-danger">Cancelled</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($row['status']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="orderDetails.php?id=<?php echo (int)$row['orderinfo_id']; ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="fa-regular fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">No orders found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
