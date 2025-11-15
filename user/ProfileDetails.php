<?php
session_start();
include("../includes/config.php");

$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    header("Location: ../user/login.php");
    exit;
}

$customer = null;
$sql = "SELECT * FROM customers WHERE user_id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result && mysqli_num_rows($result) > 0) {
    $customer = mysqli_fetch_assoc($result);
}
mysqli_stmt_close($stmt);

if (isset($_POST['submit'])) {
    $lname   = trim($_POST['lname']);
    $fname   = trim($_POST['fname']);
    $title   = trim($_POST['title']);
    $address = trim($_POST['address']);
    $town    = trim($_POST['town']);
    $zipcode = trim($_POST['zipcode']);
    $phone   = trim($_POST['phone']);

    if ($customer) {
        $upd = mysqli_prepare($conn, "UPDATE customers SET title=?, lname=?, fname=?, addressline=?, town=?, zipcode=?, phone=? WHERE user_id=?");
        mysqli_stmt_bind_param($upd, 'sssssssi', $title, $lname, $fname, $address, $town, $zipcode, $phone, $user_id);
        $ok = mysqli_stmt_execute($upd);
        mysqli_stmt_close($upd);
    } else {
        $ins = mysqli_prepare($conn, "INSERT INTO customers (title, lname, fname, addressline, town, zipcode, phone, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($ins, 'sssssssi', $title, $lname, $fname, $address, $town, $zipcode, $phone, $user_id);
        $ok = mysqli_stmt_execute($ins);
        mysqli_stmt_close($ins);
    }

    if (!empty($ok)) {
        // ✅ Auto-login refresh
        $sqlUser = "SELECT id, email, role FROM users WHERE id = ?";
        $stmtUser = mysqli_prepare($conn, $sqlUser);
        mysqli_stmt_bind_param($stmtUser, 'i', $user_id);
        mysqli_stmt_execute($stmtUser);
        $resultUser = mysqli_stmt_get_result($stmtUser);
        if ($resultUser && mysqli_num_rows($resultUser) > 0) {
            $userRow = mysqli_fetch_assoc($resultUser);
            $_SESSION['user_id'] = $userRow['id'];
            $_SESSION['email']   = $userRow['email'];
            $_SESSION['role']    = $userRow['role'] ?? 'user';
        }
        header("Location: ../index.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!doctype html>
<html>
<head>
  <title>Profile Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/F&LGlamCo/includes/style/style.css?v=2">
</head>
<body>
<div class="profile-details-container">
  <h3>Fill in Profile Details</h3>
  <form method="POST">
    <div class="mb-3">
      <label>First Name</label>
      <input type="text" name="fname" class="form-control" 
             value="<?php echo htmlspecialchars($customer['fname'] ?? ''); ?>" required>
    </div>
    <div class="mb-3">
      <label>Last Name</label>
      <input type="text" name="lname" class="form-control" 
             value="<?php echo htmlspecialchars($customer['lname'] ?? ''); ?>" required>
    </div>
    <div class="mb-3">
      <label>Title</label>
      <input type="text" name="title" class="form-control" 
             value="<?php echo htmlspecialchars($customer['title'] ?? ''); ?>">
    </div>
    <div class="mb-3">
      <label>Address</label>
      <input type="text" name="address" class="form-control" 
             value="<?php echo htmlspecialchars($customer['addressline'] ?? ''); ?>">
    </div>
    <div class="mb-3">
      <label>Town</label>
      <input type="text" name="town" class="form-control" 
             value="<?php echo htmlspecialchars($customer['town'] ?? ''); ?>">
    </div>
    <div class="mb-3">
      <label>Zipcode</label>
      <input type="text" name="zipcode" class="form-control" 
             value="<?php echo htmlspecialchars($customer['zipcode'] ?? ''); ?>">
    </div>
    <div class="mb-3">
      <label>Phone</label>
      <input type="text" name="phone" class="form-control" 
             value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>">
    </div>
    <button type="submit" name="submit" class="btn btn-primary">Save Details</button>
  </form>
</div>
</body>
</html>
