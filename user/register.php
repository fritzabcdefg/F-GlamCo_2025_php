<?php
session_start();
include("../includes/config.php");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email   = isset($_POST['email']) ? trim($_POST['email']) : '';
    $pass    = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm = isset($_POST['confirmPass']) ? trim($_POST['confirmPass']) : '';

    if ($email !== '' && $pass !== '' && $confirm !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if ($pass !== $confirm) {
            $error = "Passwords do not match.";
        } else {
            // Check if email already exists
            $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
            mysqli_stmt_bind_param($check, 's', $email);
            mysqli_stmt_execute($check);
            mysqli_stmt_store_result($check);

            if (mysqli_stmt_num_rows($check) === 0) {
                // For production, use password_hash() instead of sha1
                $hashed = sha1($pass);

                $insert = mysqli_prepare($conn, "INSERT INTO users (email, password, role, active) VALUES (?, ?, 'customer', 1)");
                mysqli_stmt_bind_param($insert, 'ss', $email, $hashed);

                if (mysqli_stmt_execute($insert)) {
                    $newUserId = mysqli_insert_id($conn);

                    // Create blank customer row linked to this user
                    $insCustomer = mysqli_prepare($conn, "INSERT INTO customers (user_id) VALUES (?)");
                    mysqli_stmt_bind_param($insCustomer, 'i', $newUserId);
                    mysqli_stmt_execute($insCustomer);
                    mysqli_stmt_close($insCustomer);

                    // Auto-login: set session
                    $_SESSION['user_id'] = $newUserId;
                    $_SESSION['email']   = $email;
                    $_SESSION['role']    = 'customer';

                    header("Location: ProfilePicture.php");
                    exit();
                } else {
                    $error = "Registration failed. Please try again.";
                }
            } else {
                $error = "Email is already registered.";
            }
            mysqli_stmt_close($check);
        }
    } else {
        $error = "Invalid input. Please check your entries.";
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

<!-- ✅ Show alert outside the container, no close button -->
<?php if (!empty($error)): ?>
  <div class="alert alert-danger text-center" role="alert">
    <?php echo htmlspecialchars($error); ?>
  </div>
<?php endif; ?>

<div class="container">
  <div class="auth-container">
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
      <div class="mb-3">
        <label for="email" class="loginform-label">Email</label>
        <input class="form-control" id="email" name="email">
      </div>

      <div class="mb-3">
        <label for="password" class="loginform-label">Password</label>
        <input class="form-control" id="password" name="password">
      </div>

      <div class="mb-3">
        <label for="confirmPass" class="loginform-label">Confirm Password</label>
        <input class="form-control" id="confirmPass" name="confirmPass">
      </div>

      <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>
  </div>
</div>

<?php include("../includes/footer.php"); ?>
</body>
</html>
