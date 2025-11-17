<?php
session_start();
include('../../includes/auth_user.php');
include('../../includes/header.php');
include('../../includes/config.php');


$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$order_id) {
    echo "<p>Invalid order ID.</p>";
    include('../includes/footer.php');
    exit;
}

// Fetch order items from view
$sql = "SELECT * FROM orderdetails WHERE orderinfo_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$items = [];
$total = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
    $total += $row['sell_price'] * $row['quantity'];
}

// Fetch order summary
$summaryQ = mysqli_query($conn, "SELECT shipping, status FROM orderinfo WHERE orderinfo_id = {$order_id} LIMIT 1");
$summary = mysqli_fetch_assoc($summaryQ);
$grand_total = $total + $summary['shipping'];
?>

<style>
    .order-details-container {
        padding: 20px;
        background: #000000ff;
    }
    .order-table {
        width: 100%;
        border-collapse: collapse;
        background: #000000ff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .order-table th, .order-table td {
        padding: 12px;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }
    .order-table th {
        background: #F8BBD0;
        color: #fff;
    }
    .order-actions {
        text-align: right;
    }
    .order-actions form {
        display: inline-block;
        margin-left: 10px;
    }
    .order-actions button {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        color: #fff;
        cursor: pointer;
    }
    .btn-cancel { background: #dc3545; }
    .btn-delivered { background: #28a745; }
</style>

<div class="order-details-container">
    <h2 style="color:#F69b9A;"> Order #<?php echo $order_id; ?> Details</h2>
    <table class="order-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>₱<?php echo number_format($item['sell_price'], 2); ?></td>
                <td>₱<?php echo number_format($item['sell_price'] * $item['quantity'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3" align="right"><strong>Shipping</strong></td>
                <td>₱<?php echo number_format($summary['shipping'], 2); ?></td>
            </tr>
            <tr>
                <td colspan="3" align="right"><strong>Grand Total</strong></td>
                <td><strong>₱<?php echo number_format($grand_total, 2); ?></strong></td>
            </tr>
        </tbody>
    </table>

    <div class="order-actions">
        <?php if ($summary['status'] === 'Pending'): ?>
            <form method="POST" action="update_order_status.php">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                <input type="hidden" name="status" value="Cancelled">
                <button class="btn-cancel">❌ Cancel Order</button>
            </form>
            <form method="POST" action="update_order_status.php">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                <input type="hidden" name="status" value="Delivered">
                <button class="btn-delivered">✅ Mark as Delivered</button>
            </form>
        <?php else: ?>
            <p><em>This order is already marked as <strong><?php echo $summary['status']; ?></strong>.</em></p>
        <?php endif; ?>
    </div>
</div>

<?php include('../../includes/footer.php'); ?>
