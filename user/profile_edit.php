<?php
session_start();
include('../includes/auth_user.php');
include("../includes/header.php");
include("../includes/config.php");

$customer = null;
$user_id = $_SESSION['user_id'] ?? 0;

// Fetch existing customer data
$sql = "SELECT * FROM customers WHERE user_id = $user_id LIMIT 1";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    $customer = mysqli_fetch_assoc($result);
}

// Handle image upload
if (isset($_POST['upload_image']) && isset($_FILES['profile_image'])) {
    $target_dir = "../uploads/";
    $filename = basename($_FILES["profile_image"]["name"]);
    $target_file = $target_dir . $filename;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $valid_types = ['jpg', 'jpeg', 'png'];
    if (in_array($imageFileType, $valid_types) && $_FILES["profile_image"]["size"] <= 5 * 1024 * 1024) {
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            mysqli_query($conn, "UPDATE customers SET image = '$filename' WHERE user_id = $user_id");
            $_SESSION['success'] = "Image uploaded successfully.";
            header("Location: profile.php");
            exit;
        } else {
            echo "Error uploading file.";
        }
    } else {
        echo "Invalid file type or size.";
    }
}

// Handle profile update
if (isset($_POST['submit'])) {
    $lname = trim($_POST['lname']);
    $fname = trim($_POST['fname']);
    $title = trim($_POST['title']);
    $address = trim($_POST['address']);
    $town = trim($_POST['town']);
    $zipcode = trim($_POST['zipcode']);
    $phone = trim($_POST['phone']);

    if ($customer) {
        $sql = "UPDATE customers SET 
                    title = '$title',
                    lname = '$lname',
                    fname = '$fname',
                    addressline = '$address',
                    town = '$town',
                    zipcode = '$zipcode',
                    phone = '$phone'
                WHERE user_id = $user_id";
    } else {
        $sql = "INSERT INTO customers (title, lname, fname, addressline, town, zipcode, phone, user_id) 
                VALUES ('$title', '$lname', '$fname', '$address', '$town', '$zipcode', '$phone', $user_id)";
    }

    $result = mysqli_query($conn, $sql);
    if ($result) {
        $_SESSION['success'] = 'Profile saved';
        header("Location: profile.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<div class="container-xl px-4 mt-4">
    <?php include("../includes/alert.php"); ?>
    <nav class="nav nav-borders">
        <a class="nav-link active ms-0" href="#">Profile</a>
    </nav>
    <hr class="mt-0 mb-4">
    <div class="row">
        <div class="col-xl-4">
            <div class="card mb-4 mb-xl-0">
                <div class="card-header">Profile Picture</div>
                <div class="card-body text-center">
                    <img class="img-account-profile rounded-circle mb-2"
                         style="width: 150px; height: 150px; object-fit: cover;"
                         src="<?php echo isset($customer['image']) ? '../uploads/' . $customer['image'] : 'http://bootdey.com/img/Content/avatar/avatar1.png'; ?>"
                         alt="Profile Image">
                    <div class="small font-italic text-muted mb-4">JPG or PNG no larger than 5 MB</div>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="file" name="profile_image" accept="image/*" class="form-control mb-3">
                        <button class="btn btn-primary" type="submit" name="upload_image">Upload new image</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Account Details</div>
                <div class="card-body">
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">
                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="inputFirstName">First name</label>
                                <input class="form-control" id="inputFirstName" type="text" name="fname"
                                       value="<?php echo htmlspecialchars($customer['fname'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="inputLastName">Last name</label>
                                <input class="form-control" id="inputLastName" type="text" name="lname"
                                       value="<?php echo htmlspecialchars($customer['lname'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="address">Address</label>
                                <input class="form-control" id="address" type="text" name="address"
                                       value="<?php echo htmlspecialchars($customer['addressline'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1" for="town">Town</label>
                                <input class="form-control" id="town" type="text" name="town"
                                       value="<?php echo htmlspecialchars($customer['town'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="zip">Zip code</label>
                                <input class="form-control" id="zip" type="tel" name="zipcode"
                                       value="<?php echo htmlspecialchars($customer['zipcode'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="title">Title (Mr./Ms./Mrs..)</label>
                            <input class="form-control" id="title" type="text" name="title"
                                   value="<?php echo htmlspecialchars($customer['title'] ?? ''); ?>">
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-6">
                                <label class="small mb-1" for="inputPhone">Phone number</label>
                                <input class="form-control" id="inputPhone" type="tel" name="phone"
                                       value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>">
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit" name="submit">Save changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
