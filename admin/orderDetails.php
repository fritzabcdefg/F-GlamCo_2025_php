<?php
// CREATE VIEW orderdetails AS 
// SELECT o.orderinfo_id, c.lname, c.fname, c.addressline, c.town, c.zipcode, c.phone,  
// i.sell_price, ol.quantity, i.description, o.status 
// FROM customer c 
// INNER JOIN orderinfo o USING(customer_id) 
// INNER JOIN orderline ol USING(orderinfo_id) 
// INNER JOIN item i USING(item_id);

session_start();
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../includes/config.php';
include __DIR__ . '/../includes/header.php';

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$_SESSION['orderId'] = $orderId;

$sql = "SELECT lname, fname, addressline, town, zipcode, phone, orderinfo_id, status 
        FROM orderdetails 
        WHERE orderinfo_id = $orderId LIMIT 1";
$result = mysqli_query($conn, $sql);
$customer = mysqli_fetch_assoc($result);

$sql = "SELECT name, quantity, sell_price 
        FROM orderdetails 
        WHERE orderinfo_id = $orderId";
$items = mysqli_query($conn, $sql);
?>

<div class="container mt-4">
    <h2>Order #<?= htmlspecialchars($customer['orderinfo_id']) ?></h2>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($customer['lname'] . ' ' . $customer['fname']) ?></h5>
            <p class="card-text">
                <?= htmlspecialchars($customer['addressline']) ?>, 
                <?= htmlspecialchars($customer['town']) ?>, 
                <?= htmlspecialchars($customer['zipcode']) ?><br>
                Phone: <?= htmlspecialchars($customer['phone']) ?><br>
                Status: <strong><?= htmlspecialchars($customer['status']) ?></strong>
            </p>
        </div>
    </div>

    <table class="table table-striped table-bordered">
        <thead style="background-color:#F8BBD0; color:#000000;">
            <tr>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Price (₱)</th>
                <th>Subtotal (₱)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $grandTotal = 0;
            while ($row = mysqli_fetch_assoc($items)): 
                $total = $row['sell_price'] * $row['quantity'];
                $grandTotal += $total;
            ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= (int)$row['quantity'] ?></td>
                <td><?= number_format($row['sell_price'], 2) ?></td>
                <td><?= number_format($total, 2) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h4 class="mt-3">Grand Total: ₱<?= number_format($grandTotal, 2) ?></h4>

        <form action="updateOrder.php" method="POST" class="mt-3">
            <input type="hidden" name="order_id" value="<?= (int)$orderId ?>">
            <div class="mb-3">
                <label for="status" class="form-label">Update Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="">Select status...</option>
                    <option value="Processing" <?= $customer['status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="Shipped" <?= $customer['status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="Delivered" <?= $customer['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="Cancelled" <?= $customer['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <button type="submit" title="Update Order"
    style="background-color:#000000; border:4px solid #ffffff; color:#ffffff; font-weight:bold; font-size:1.0rem; text-align:center; cursor:pointer; padding:8px 17px; border-radius:4px; display:block; margin-left:auto; margin-right:0;">
    Update Order
</button>


</form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
