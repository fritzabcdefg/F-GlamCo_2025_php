<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); } 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>F & L Glam Co</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
  <link href="/F&LGlamCo/includes/style/style.css" rel="stylesheet" type="text/css">
</head>
<body>

<!-- Hidden checkbox for toggle -->
<input type="checkbox" id="sidebarToggle" hidden>

<!-- Sidebar -->
<div id="mySidebar" class="sidebar">
  <div class="sidebar-panel text-center">

    <!-- Close button -->
    <label for="sidebarToggle" class="closebtn"><i class="fas fa-times"></i> Close</label>

    <?php
      // Fetch customer info if available
      $img = isset($customer['image']) && $customer['image'] !== '' 
          ? '../uploads/' . $customer['image'] 
          : 'http://bootdey.com/img/Content/avatar/avatar1.png';

      $displayName = trim(($customer['fname'] ?? '') . ' ' . ($customer['lname'] ?? ''));
    ?>

    <!-- Profile picture -->
    <img src="<?php echo $img; ?>" alt="Profile" 
         class="rounded-circle mb-3" 
         style="width:100px;height:100px;object-fit:cover;">

    <!-- Name -->
    <h6 class="text-white mb-3"><?php echo htmlspecialchars($displayName ?: 'Guest'); ?></h6>

    <!-- Buttons based on role -->
    <a href="/F&LGlamCo/user/profile.php" class="sidebar-btn">View Profile</a>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
      <a href="/F&LGlamCo/product/index.php" class="sidebar-btn">Items</a>
      <a href="/F&LGlamCo/admin/users.php" class="sidebar-btn">Users</a>
      <a href="/F&LGlamCo/admin/orders.php" class="sidebar-btn">Orders</a>
    <?php else: ?>
      <a href="/F&LGlamCo/user/orders/orders.php" class="sidebar-btn">My Orders</a>
    <?php endif; ?>

    <!-- Bottom section -->
    <div class="bottom mt-auto">
      <p class="sidebar-email text-white mb-2">
        <?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>
      </p>
      <a href="/F&LGlamCo/user/logout.php" class="sidebar-btn logout">Logout</a>
    </div>
  </div>
</div>


<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm flex-column" style="background-color: #000;">
  <!-- Top row: Brand -->
  <div class="w-100 d-flex justify-content-between align-items-center py-2">
    <span class="navbar-brand text-white fw-bold fs-4 mx-auto">F & L GLAM CO.</span>
  </div>

  <!-- Bottom row: Menus -->
  <div class="container-fluid d-flex justify-content-between align-items-center w-100">
    <!-- Left Section -->
    <div class="d-flex align-items-center gap-3">
      <a class="nav-link text-pink" href="/F&LGlamCo/index.php"><i class="fas fa-home"></i></a>
      <form action="/F&LGlamCo/search.php" method="GET" class="d-flex search-bar">
        <input class="form-control form-control-sm border-pink" type="search" placeholder="Search" name="search">
        <button class="btn btn-sm text-pink" type="submit"><i class="fas fa-search"></i></button>
      </form>
      <a class="nav-link text-pink <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" href="/F&LGlamCo/index.php">All</a>
      <a class="nav-link text-pink" href="#">Eye Makeup</a>
      <a class="nav-link text-pink" href="#">Face Makeup</a>
      <a class="nav-link text-pink" href="#">Lip Makeup</a>
    </div>

    <!-- Right Section -->
    <ul class="navbar-nav d-flex align-items-center gap-3">
<<<<<<< HEAD
      <li class="nav-item position-relative">
        <a class="nav-link text-pink" href="/F&LGlamCo/cart/view_cart.php">
          <i class="fas fa-shopping-bag"></i>
          <?php 
            $cart_count = isset($_SESSION['cart_products']) ? count($_SESSION['cart_products']) : 0;
            if ($cart_count > 0): 
          ?>
            <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle">
              <?php echo $cart_count; ?>
            </span>
          <?php endif; ?>
        </a>
=======
      <li class="nav-item">
        <a class="nav-link text-pink" href="/F&LGlamCo/cart/view_cart.php"><i class="fas fa-shopping-bag"></i> </a>
>>>>>>> 492ee80932179d09eb8985187f5a42c6870080b3
      </li>

      <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Sidebar toggle beside the bag -->
        <li class="nav-item">
          <label for="sidebarToggle" class="nav-link text-pink" style="cursor:pointer;">
            <i class="fas fa-bars"></i> MENU
          </label>
        </li>
      <?php endif; ?>

      <?php if (!isset($_SESSION['user_id'])): ?>
        <li class="nav-item">
          <a class="nav-link text-pink" href="/F&LGlamCo/user/login.php"><i class="fas fa-user"></i> LOGIN</a>
        </li>
      <?php endif; ?>
    </ul>
  </div>
</nav>
