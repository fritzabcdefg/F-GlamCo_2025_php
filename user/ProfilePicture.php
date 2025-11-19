<?php
session_start();
include("../includes/config.php");

$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) {
    header("Location: ../user/login.php");
    exit;
}

// Fetch current image (if any)
$img = 'http://bootdey.com/img/Content/avatar/avatar1.png';
$sql = "SELECT image FROM customers WHERE user_id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    if (!empty($row['image'])) {
        $img = '../uploads/' . $row['image'];
    }
}
mysqli_stmt_close($stmt);

$error = '';
if (isset($_POST['upload_image']) && isset($_FILES['profile_image'])) {
    $target_dir = "../uploads/";
    $filename = basename($_FILES["profile_image"]["name"]);
    $target_file = $target_dir . $filename;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $valid_types = ['jpg','jpeg','png'];

    if (in_array($imageFileType, $valid_types) && $_FILES["profile_image"]["size"] <= 5*1024*1024) {
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $updImg = mysqli_prepare($conn, "UPDATE customers SET image = ? WHERE user_id = ?");
            mysqli_stmt_bind_param($updImg, 'si', $filename, $user_id);
            mysqli_stmt_execute($updImg);
            mysqli_stmt_close($updImg);

            header("Location: ProfileDetails.php");
            exit;
        } else {
            $error = "Error uploading file.";
        }
    } else {
        $error = "Invalid file type or size.";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Upload Profile Picture - F&L Glam Co</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Use the same absolute path as header -->
  <link rel="stylesheet" href="/F&LGlamCo/includes/style/style.css?v=1">
</head>
<body>

<div class="container">
  <div class="auth-container text-center">
    <img src="<?php echo $img; ?>" alt="Profile Image" class="profile-image mb-3">
    <h5 style="color:#000000;"> <?php echo htmlspecialchars($_SESSION['email'] ?? 'Your Account'); ?></h5>

    <?php if (!empty($error)): ?>
      <div class="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="mt-3">
      <input type="file" name="profile_image" accept="image/*" class="form-control mb-3" required>
      <button class="btn btn-primary" type="submit" name="upload_image">Upload new image</button>
    </form>
  </div>
</div>

</body>
</html>
