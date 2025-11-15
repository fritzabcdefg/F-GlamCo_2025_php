<?php
session_start();
include("../includes/config.php");
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - F&L Glam Co</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../includes/style/style.css">
</head>
<body>
  
<?php
if (isset($_POST['submit'])) {
  $email = isset($_POST['email']) ? trim($_POST['email']) : '';
  $pass  = isset($_POST['password']) ? trim($_POST['password']) : '';

  // server-side validation
  if ($email !== '' && $pass !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $sql = "SELECT id, email, password, role, active FROM users WHERE email=? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $user_id, $email_db, $hashedPassword, $role, $active);

    if (mysqli_stmt_num_rows($stmt) === 1) {
      mysqli_stmt_fetch($stmt);

      $login_ok = false;
      $needs_rehash = false;

      // Prefer PHP's password_verify for modern hashes
      if (password_verify($pass, $hashedPassword)) {
          $login_ok = true;
      } elseif (hash_equals($hashedPassword, sha1($pass))) {
          // Legacy SHA1 match — accept login but mark for re-hash
          $login_ok = true;
          $needs_rehash = true;
      }

      if ($login_ok && $active) {
          if ($needs_rehash) {
              $newHash = password_hash($pass, PASSWORD_DEFAULT);
              $up = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
              if ($up) {
                  mysqli_stmt_bind_param($up, 'si', $newHash, $user_id);
                  mysqli_stmt_execute($up);
                  mysqli_stmt_close($up);
              }
          }

          $_SESSION['email']   = $email_db;
          $_SESSION['user_id'] = $user_id;
          $_SESSION['role']    = $role;
          header("Location: ../user/profile.php");
          exit();
      }
    }
  }
}
?>
<div class="container">
  <div class="auth-container">
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
      <div class="mb-3">
        <label for="form2Example1" class="form-label">Email address</label>
        <input type="email" id="form2Example1" class="form-control" name="email" required />
      </div>
      <div class="mb-3">
        <label for="form2Example2" class="form-label">Password</label>
        <input type="password" id="form2Example2" class="form-control" name="password" required />
      </div>
      <button type="submit" class="btn btn-primary w-100 mb-3" name="submit">Sign in</button>
      <div class="text-center">
        <p>Not a member? <a href="register.php">Register</a></p>
      </div>
    </form>
  </div>
</div>

<?php include("../includes/footer.php"); ?>
