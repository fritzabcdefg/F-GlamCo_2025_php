<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// ✅ Admin-only access enforcement
if (!isset($_SESSION['user_id'])) {
    header("Location: ../user/login.php?error=unauthorized");
    exit();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?error=adminonly");
    exit();
}

include __DIR__ . '/../includes/header.php';

// Query sales from salesperorder view
$sql = "SELECT orderinfo_id, total, status FROM salesperorder ORDER BY total DESC";
$result = mysqli_query($conn, $sql);
$itemCount = $result ? mysqli_num_rows($result) : 0;

$grandTotal = 0;
if ($result) {
    foreach ($result as $row) {
        $grandTotal += $row['total'];
    }
}
?>

<div class="container mt-4">
    <h2 class="mb-4" style="color:#ffffff; font-weight:700;">Sales Report</h2>

    <!-- Sales count -->
    <div class="alert alert-info">
        Total Orders: <?= $itemCount ?>
    </div>

    <!-- Sales Table -->
    <table class="table table-striped" style="border:1px solid #F8BBD0; border-radius:10px; overflow:hidden;">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Total Sales</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($itemCount > 0): ?>
                <?php foreach ($result as $row): ?>
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
                            <a href="orderDetails.php?id=<?= (int)$row['orderinfo_id']; ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="fa-regular fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <!-- Grand Total Row -->
                <tr style="font-weight:bold; background:#f8f9fa;">
                    <td colspan="1">Grand Total</td>
                    <td>₱<?= number_format($grandTotal, 2); ?></td>
                    <td colspan="2"></td>
                </tr>
            <?php else: ?>
                <tr><td colspan="4">No sales records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
