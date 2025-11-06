<?php
session_start();
include("../includes/header.php");
include("../includes/config.php");

if (isset($_POST['submit'])) {
  $email = trim($_POST['email']);
  $pass = trim($_POST['password']);

  $sql = "SELECT id, email, password, role FROM users WHERE email=? LIMIT 1";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, 's', $email);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_store_result($stmt);
  mysqli_stmt_bind_result($stmt, $user_id, $email, $hashedPassword, $role);

  if (mysqli_stmt_num_rows($stmt) === 1) {
    mysqli_stmt_fetch($stmt);
    if (hash_equals($hashedPassword, sha1($pass))) {
      $_SESSION['email'] = $email;
      $_SESSION['user_id'] = $user_id;
      $_SESSION['role'] = $role;
      header("Location: ../user/profile.php");
    } else {
      $_SESSION['message'] = 'Wrong email or password';
    }
  }
}
?>
<div class="container">
  <div class="auth-container">
    <?php include("../includes/alert.php"); ?>
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