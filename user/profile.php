<?php
session_start();
include("../includes/config.php");

$user_id = $_SESSION['user_id'] ?? 0;
$customer = null;

if ($user_id) {
    $sql = "SELECT c.*, u.email 
            FROM customers c 
            LEFT JOIN users u ON u.id = c.user_id 
            WHERE c.user_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        $customer = mysqli_fetch_assoc($result);
    }
}
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register - F&L Glam Co</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../includes/style/style.css">
</head>
<body>

<div class="container px-4 mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card profile-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Customer Profile</h5>
                    <a href="profile_edit.php" class="btn btn-sm btn-primary">Edit</a>
                </div>
                <div class="card-body">
                    <?php if (!$user_id): ?>
                        <p>Please <a href="login.php">log in</a> to view your profile.</p>
                    <?php else: ?>
                        <?php if (!$customer): ?>
                            <p>No profile found. Click Edit to create your profile.</p>
                        <?php else: ?>
                            <div class="row">
                                <div class="col-md-4 text-center">
                                    <?php
                                        $img = isset($customer['image']) && $customer['image'] !== '' 
                                            ? '../uploads/' . $customer['image'] 
                                            : 'http://bootdey.com/img/Content/avatar/avatar1.png';
                                        $displayName = trim(
                                            ($customer['title'] ?? '') . ' ' .
                                            ($customer['fname'] ?? '') . ' ' .
                                            ($customer['lname'] ?? '')
                                        );
                                    ?>
                                    <img src="<?php echo $img; ?>" alt="Profile Image" class="profile-image mb-3">
                                    <?php if ($displayName): ?>
                                        <h5 class="mt-2"><?php echo htmlspecialchars($displayName); ?></h5>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-8">
                                    <table class="table table-borderless profile-table">
                                        <tbody>
                                            <tr>
                                                <th scope="row">Name</th>
                                                <td><?php echo htmlspecialchars(($customer['fname'] ?? '') . ' ' . ($customer['lname'] ?? '')); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Email</th>
                                                <td><?php echo htmlspecialchars($customer['email'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Address</th>
                                                <td><?php echo htmlspecialchars($customer['addressline'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Town</th>
                                                <td><?php echo htmlspecialchars($customer['town'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Zipcode</th>
                                                <td><?php echo htmlspecialchars($customer['zipcode'] ?? ''); ?></td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Phone</th>
                                                <td><?php echo htmlspecialchars($customer['phone'] ?? ''); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
</body>
</html>
