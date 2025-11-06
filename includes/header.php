<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
  <link href="/F&LGlamCo/includes/style/style.css" rel="stylesheet" type="text/css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <title>My Shop</title>
  <style>
    body {
      font-family: 'Montserrat', sans-serif;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
      <a class="navbar-brand" href="/F&LGlamCo/index.php">My Shop</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" href="/F&LGlamCo/index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/F&LGlamCo/link.php">Link</a>
          </li>
          <li class="nav-item dropdown">
            <?php if (isset($_SESSION['user_id'])): ?>
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Dropdown</a>
              <ul class="dropdown-menu">
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                  <li><a class="dropdown-item" href="/F&LGlamCo/product/index.php">Items</a></li>
                  <li><a class="dropdown-item" href="/F&LGlamCo/admin/orders.php">Orders</a></li>
                  <li><a class="dropdown-item" href="/F&LGlamCo/admin/users.php">Users</a></li>
                <?php else: ?>
                  <li><a class="dropdown-item" href="/F&LGlamCo/user/profile.php">Profile</a></li>
                  <li><a class="dropdown-item" href="/F&LGlamCo/user/myorders.php">My Orders</a></li>
                <?php endif; ?>
              </ul>
            <?php endif; ?>
          </li>
        </ul>

        <form action="/F&LGlamCo/search.php" method="GET" class="d-flex me-3">
          <input class="form-control me-2" type="search" placeholder="Search" name="search">
          <button class="btn btn-outline-success" type="submit">Search</button>
        </form>

        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <?php if (!isset($_SESSION['user_id'])): ?>
            <li class="nav-item">
              <a href="/F&LGlamCo/user/login.php" class="nav-link">Login</a>
            </li>
          <?php else: ?>
            <li class="nav-item d-flex align-items-center">
              <span class="nav-link"><?= isset($_SESSION['email']) ? $_SESSION['email'] : 'Welcome!' ?></span>
            </li>
            <li class="nav-item">
              <a href="/F&LGlamCo/user/logout.php" class="nav-link">Logout</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>
</body>
</html>
