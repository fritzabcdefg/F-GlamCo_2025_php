<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>F & L Glam Co</title>

  <!-- External Styles & Fonts -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">

  <!-- ✅ Your Custom Stylesheet -->
  <link href="/F&LGlamCo/includes/style/style.css" rel="stylesheet" type="text/css">

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #000;">
  <div class="container-fluid d-flex justify-content-between align-items-center">

    <!-- Left Section -->
    <div class="d-flex align-items-center gap-3">
      <a class="nav-link text-pink" href="/F&LGlamCo/index.php">
        <i class="fas fa-home"></i>
      </a>

      <form action="/F&LGlamCo/search.php" method="GET" class="d-flex search-bar">
        <input class="form-control form-control-sm border-pink" type="search" placeholder="Search" name="search">
        <button class="btn btn-sm text-pink" type="submit"><i class="fas fa-search"></i></button>
      </form>

      <a class="nav-link text-pink <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" href="/F&LGlamCo/index.php">All</a>
      <a class="nav-link text-pink" href="#">Eye Makeup</a>
      <a class="nav-link text-pink" href="#">Face Makeup</a>
      <a class="nav-link text-pink" href="#">Lip Makeup</a>
    </div>

    <!-- Center Brand -->
    <div class="navbar-brand mx-auto text-white fw-bold fs-4">F & L GLAM CO.</div>

    <!-- Right Section -->
    <ul class="navbar-nav d-flex align-items-center gap-3">
      <li class="nav-item">
        <a class="nav-link text-pink" href="#"><i class="fas fa-shopping-bag"></i> MY BAG</a>
      </li>
      <?php if (!isset($_SESSION['user_id'])): ?>
        <li class="nav-item">
          <a class="nav-link text-pink <?php echo basename($_SERVER['PHP_SELF']) === 'login.php' ? 'active' : ''; ?>" href="/F&LGlamCo/user/login.php">
            <i class="fas fa-user"></i> LOGIN
          </a>
        </li>
      <?php else: ?>
        <li class="nav-item">
          <span class="nav-link text-white"><?= $_SESSION['email'] ?? 'Welcome!' ?></span>
        </li>
        <li class="nav-item">
          <a class="nav-link text-pink" href="/F&LGlamCo/user/logout.php">Logout</a>
        </li>
      <?php endif; ?>
    </ul>
  </div>
</nav>
