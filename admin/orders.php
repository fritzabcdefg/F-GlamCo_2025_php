<?php
session_start();
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';

$sql = "SELECT orderinfo_id, total, status FROM salesperorder ORDER BY orderinfo_id DESC";
$result = mysqli_query($conn, $sql);
$itemCount = $result ? mysqli_num_rows($result) : 0;
?>

<div class="container mt-4">
    <h2 class="mb-4" style="color:#ffffff; font-weight:700;">Orders</h2>

    <!-- Order count -->
    <div class="alert alert-info">
        Total Orders: <?= $itemCount ?>
    </div>

    <!-- Orders Table -->
    <table class="table table-striped" style="border:1px solid #F8BBD0; border-radius:10px; overflow:hidden;">
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
                        <td><?= (int)$row['orderinfo_id']; ?></td>
                        <td>₱<?= number_format($row['total'], 2); ?></td>
                        <td>
                            <?php if ($row['status'] === 'Delivered'): ?>
                                <span class="badge bg-success">Delivered</span>
                            <?php elseif ($row['status'] === 'Cancelled'): ?>
                                <span class="badge bg-danger">Cancelled</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><?= htmlspecialchars($row['status']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- View details -->
                            <a href="orderDetails.php?id=<?= (int)$row['orderinfo_id']; ?>" 
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
