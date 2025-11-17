<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); } 
include(__DIR__ . "/config.php");

// Defaults
$img = '/F&LGlamCo/assets/default-avatar.png';
$displayName = 'Guest';
$orderCount = 0;

if (isset($_SESSION['user_id'])) {
    $userId = intval($_SESSION['user_id']);
    $res = mysqli_query($conn, "SELECT fname, lname, image FROM customers WHERE customer_id = {$userId} LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $displayName = trim(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? ''));
        if (!empty($row['image'])) {
            $img = '/F&LGlamCo/uploads/' . $row['image'];
        }
    }

    // Order count
    $orderRes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM orderinfo WHERE customer_id = {$userId}");
    if ($orderRes) {
        $orderRow = mysqli_fetch_assoc($orderRes);
        $orderCount = intval($orderRow['total']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>F & L Glam Co</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
  <link href="/F&LGlamCo/includes/style/style.css" rel="stylesheet" type="text/css">


</head>
<body>

<!-- Hidden checkboxes -->
<input type="checkbox" id="sidebarToggle" hidden>
<input type="checkbox" id="logoutConfirm" hidden>

<!-- Hidden checkbox for toggle -->
<input type="checkbox" id="sidebarToggle" hidden>

<!-- Sidebar -->
<div id="mySidebar" class="sidebar">
  <div class="sidebar-panel text-center">
    <label for="sidebarToggle" class="closebtn"><i class="fas fa-times"></i> Close</label>

    <img src="<?php echo htmlspecialchars($img); ?>" alt="Profile Picture"
         class="rounded-circle mb-2" style="width:100px;height:100px;object-fit:cover;">
    <h6 class="text-white mb-3"><?php echo htmlspecialchars($displayName); ?></h6>

    <a href="/F&LGlamCo/user/profile.php" class="sidebar-btn">View Profile</a>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
      <a href="/F&LGlamCo/product/index.php" class="sidebar-btn">Items</a>
      <a href="/F&LGlamCo/admin/users.php" class="sidebar-btn">Users</a>
      <a href="/F&LGlamCo/admin/orders.php" class="sidebar-btn">Orders</a>
      <a href="/F&LGlamCo/admin/reviews.php" class="sidebar-btn">Reviews</a>
      <a href="/F&LGlamCo/admin/sales.php" class="sidebar-btn">Sales</a>
    <?php else: ?>
      <!-- My Orders button with badge top-left -->
      <div class="position-relative d-inline-block w-100">
        <a href="/F&LGlamCo/user/orders/orders.php" 
           class="sidebar-btn d-block text-center position-relative">
          My Orders
          <?php if ($orderCount > 0): ?>
            <span class="badge bg-danger rounded-pill position-absolute top-0 start-0 translate-middle">
              <?php echo $orderCount; ?>
            </span>
          <?php endif; ?>
        </a>
      </div>
    <?php endif; ?>

    <div class="bottom mt-auto">
      <p class="sidebar-email text-white mb-2"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
      <a href="/F&LGlamCo/user/logout.php" class="sidebar-btn logout">Logout</a>
    </div>
  </div>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm flex-column">
  <div class="w-100 d-flex justify-content-between align-items-center py-2">
    <span class="navbar-brand text-white fw-bold fs-4 mx-auto">F & L GLAM CO.</span>
  </div>

  <div class="container-fluid d-flex justify-content-between align-items-center w-100">
    <!-- Left Section -->
    <div class="d-flex align-items-center gap-3">
      <a class="nav-link text-pink" href="/F&LGlamCo/index.php"><i class="fas fa-home"></i></a>
      <form action="/F&LGlamCo/search.php" method="GET" class="d-flex search-bar">
        <input class="form-control form-control-sm border-pink" type="search" placeholder="Search" name="search">
        <button class="btn btn-sm text-pink" type="submit"><i class="fas fa-search"></i></button>
      </form>
      <a class="nav-link text-pink" href="/F&LGlamCo/index.php">All</a>
      <a class="nav-link text-pink" href="/F&LGlamCo/index.php?category=Eye Makeup">Eye Makeup</a>
      <a class="nav-link text-pink" href="/F&LGlamCo/index.php?category=Face Makeup">Face Makeup</a>
      <a class="nav-link text-pink" href="/F&LGlamCo/index.php?category=Lip Makeup">Lip Makeup</a>
    </div>

    <!-- Right Section -->
    <ul class="navbar-nav d-flex align-items-center gap-3">
      <!-- Cart -->
      <li class="nav-item position-relative">
        <a class="nav-link text-pink" href="/F&LGlamCo/cart/view_cart.php">
          <i class="fas fa-shopping-bag"></i>
          <?php 
            $cart_count = isset($_SESSION['cart_products']) ? count($_SESSION['cart_products']) : 0;
            if ($cart_count > 0): ?>
              <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle">
                <?php echo $cart_count; ?>
              </span>
          <?php endif; ?>
        </a>
      </li>

      <!-- Sidebar toggle or login -->
      <?php if (isset($_SESSION['user_id'])): ?>
        <li class="nav-item">
          <label for="sidebarToggle" class="nav-link text-pink" style="cursor:pointer;">
            <i class="fas fa-bars"></i> MENU
          </label>
        </li>
      <?php else: ?>
        <li class="nav-item">
          <a class="nav-link text-pink" href="/F&LGlamCo/user/login.php"><i class="fas fa-user"></i> LOGIN</a>
        </li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

</body>
</html>
