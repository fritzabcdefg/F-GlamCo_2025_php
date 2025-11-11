<?php
session_start();
include("../includes/config.php");
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';
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

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // CSRF check
  if (!isset($_POST['csrf_token']) || !csrf_verify($_POST['csrf_token'])) {
    flash_set('Invalid form submission.', 'danger');
  } else {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $pass = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm = isset($_POST['confirmPass']) ? trim($_POST['confirmPass']) : '';

    // Validation
    if ($email === '' || $pass === '' || $confirm === '') {
      flash_set('All fields are required.', 'danger');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      flash_set('Please enter a valid email address.', 'danger');
    } elseif ($pass !== $confirm) {
      flash_set('Passwords do not match.', 'danger');
    } else {
      // Check if email already exists
      $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
      mysqli_stmt_bind_param($check, 's', $email);
      mysqli_stmt_execute($check);
      mysqli_stmt_store_result($check);

      if (mysqli_stmt_num_rows($check) > 0) {
        flash_set('Email is already registered.', 'warning');
      } else {
        $hashed = sha1($pass); // SHA-1 hashing
        $insert = mysqli_prepare($conn, "INSERT INTO users (email, password, role, active) VALUES (?, ?, 'user', 1)");
        mysqli_stmt_bind_param($insert, 'ss', $email, $hashed);
        if (mysqli_stmt_execute($insert)) {
          flash_set('Registration successful. You may now log in.', 'success');
          header("Location: login.php");
          exit();
        } else {
          flash_set('Registration failed. Please try again.', 'danger');
        }
      }
      mysqli_stmt_close($check);
    }
  }
}
?>

<div class="container">
  <div class="auth-container">
    <?php include("../includes/alert.php"); ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
      <?php echo csrf_input(); ?>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>

      <div class="mb-3">
        <label for="confirmPass" class="form-label">Confirm Password</label>
        <input type="password" class="form-control" id="confirmPass" name="confirmPass" required>
      </div>

      <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>
  </div>
</div>

<?php include("../includes/footer.php"); ?>
