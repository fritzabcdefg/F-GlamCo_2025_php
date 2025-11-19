<?php
session_start();
include('../../includes/auth_user.php');
include('../../includes/header.php');
include('../../includes/config.php');


// Get customer_id for logged-in user
$customer_id = null;
$selCust = mysqli_prepare($conn, "SELECT customer_id FROM customers WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($selCust, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($selCust);
mysqli_stmt_bind_result($selCust, $customer_id);
mysqli_stmt_fetch($selCust);
mysqli_stmt_close($selCust);

// Fetch orders for this customer
$sql = "SELECT o.orderinfo_id, o.date_placed, o.date_shipped, o.shipping, o.status,
               (SELECT SUM(ol.quantity * i.sell_price)
                FROM orderline ol
                JOIN items i ON ol.item_id = i.item_id
                WHERE ol.orderinfo_id = o.orderinfo_id) AS subtotal
        FROM orderinfo o
        WHERE o.customer_id = ?
        ORDER BY o.status ASC, o.date_placed DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<style>
    .orders-container {
        width: 100%;
        min-height: 100vh;
        padding: 20px;
        box-sizing: border-box;
        background: #000000;
    }
    .orders-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        color:#000000;
    }
    .orders-table th, .orders-table td {
        padding: 12px;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }
    .orders-table th {
        background: #F8BBD0;
        color: #000000ff;
    }
    .status-badge {
        padding: 4px 10px;
        border-radius: 4px;
        color: #000000ff;
        font-weight: bold;
    }
    .status-pending { background: #ffc107; }
    .status-shipped { background: #28a745; }
    .status-cancelled { background: #dc3545; }
</style>

<div class="orders-container">
    <h1 align="center" style="color:#F69b9A;"> My Orders</h1>
    <table class="orders-table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Date Placed</th>
                <th>Status</th>
                <th>Shipping</th>
                <th>Subtotal</th>
                <th>Total</th>
                <th>Actions</th>
                <th>Reviews</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): 
                $total = $row['subtotal'] + $row['shipping'];
                $statusClass = 'status-' . strtolower($row['status']);
            ?>
            <tr>
                <td><?php echo $row['orderinfo_id']; ?></td>
                <td><?php echo date('M d, Y', strtotime($row['date_placed'])); ?></td>
                <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                <td>₱<?php echo number_format($row['shipping'], 2); ?></td>
                <td>₱<?php echo number_format($row['subtotal'], 2); ?></td>
                <td><strong>₱<?php echo number_format($total, 2); ?></strong></td>
                <td>
                <a href="order_details.php?id=<?php echo $row['orderinfo_id']; ?>" 
                class="btn btn-sm btn-primary">View</a>
                 </td>
                <td>
                    <?php if ($row['status'] === 'Delivered'): ?>
                       <a href="../../product/reviews/review.php?order=<?php echo $row['orderinfo_id']; ?>&index=0" 
                     class="btn btn-sm btn-primary">Review</a>
                    <?php else: ?>
                        <button class="btn btn-sm btn-secondary" disabled>Review</button>
                    <?php endif; ?>
                </td>

            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include('../../includes/footer.php'); ?>