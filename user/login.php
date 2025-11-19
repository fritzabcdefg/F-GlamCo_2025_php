<?php
session_start();
include("../includes/config.php");

$deactivated = false; 
$login_error = false; 
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

      if (password_verify($pass, $hashedPassword)) {
          $login_ok = true;
      } elseif (hash_equals($hashedPassword, sha1($pass))) {
          $login_ok = true;
          $needs_rehash = true;
      }

      if ($login_ok) {
          if ($active) {
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
              header("Location: ../index.php");
              exit();
          } else {
              // account is deactivated
              $deactivated = true;
          }
      } else {
          // password didn’t match
          $login_error = true;
      }
    } else {
      // no user found with that email
      $login_error = true;
    }
  } else {
    $login_error = true;
  }
}
?>

<div class="container">

  <!-- Unauthorized/Admin alerts -->
  <?php if (isset($_GET['error'])): ?>
    <?php if ($_GET['error'] === 'unauthorized'): ?>
      <div class="alert alert-danger text-center">
        You must log in to access that page.
      </div>
    <?php elseif ($_GET['error'] === 'adminonly'): ?>
      <div class="alert alert-warning text-center">
        Admin access required to view that page.
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <!-- Deactivated account -->
  <?php if ($deactivated): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <span class="d-block text-center">That account is no longer available or deactivated.</span>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <!-- Wrong email/password -->
  <?php if ($login_error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <span class="d-block text-center">Invalid email or password. Please try again.</span>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <div class="auth-container">
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
      <div class="mb-3">
        <label for="form2Example1" class="loginform-label">Email address</label>
        <input type="email" id="form2Example1" class="form-control" name="email" required />
      </div>
      <div class="mb-3">
        <label for="form2Example2" class="loginform-label">Password</label>
        <input type="password" id="form2Example2" class="form-control" name="password" required />
      </div>
      <button type="submit" class="btn btn-primary w-100 mb-3" name="submit">Sign in</button>
      <div class="text-center">
        <p style="color:#000000">Not a member? <a href="register.php">Register</a></p>
      </div>
    </form>
  </div>
</div>

<?php include("../includes/footer.php"); ?>
