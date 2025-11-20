<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../user/login.php?error=unauthorized");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?error=adminonly");
    exit();
}

if (!isset($_POST['submit'])) {
    header("Location: create.php");
    exit();
}

$name        = trim($_POST['name']);
$description = trim($_POST['description'] ?? '');
$category_id = $_POST['category_id'] !== '' ? intval($_POST['category_id']) : null;
$quantity    = intval($_POST['quantity']);
$supplier    = trim($_POST['supplier_name'] ?? 'Default Supplier');

$cost_price = round((float)$_POST['cost_price'], 2);
$sell_price = round((float)$_POST['sell_price'], 2);

try {
    $conn->begin_transaction();

    if ($category_id === null) {
        $sql = "INSERT INTO items (name, description, cost_price, sell_price, supplier_name, category_id)
                VALUES (?, ?, ?, ?, ?, NULL)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssdss", $name, $description, $cost_price, $sell_price, $supplier);
    } else {
        $sql = "INSERT INTO items (name, description, cost_price, sell_price, supplier_name, category_id)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssdssi", $name, $description, $cost_price, $sell_price, $supplier, $category_id);
    }
    $ok = mysqli_stmt_execute($stmt);
    $itemId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    if (!$ok || $itemId <= 0) {
        throw new Exception("Error inserting item: " . mysqli_error($conn));
    }

    $sqlStock = "INSERT INTO stocks (item_id, quantity) VALUES (?, ?)";
    $stmtStock = mysqli_prepare($conn, $sqlStock);
    mysqli_stmt_bind_param($stmtStock, "ii", $itemId, $quantity);
    mysqli_stmt_execute($stmtStock);
    mysqli_stmt_close($stmtStock);

    if (!empty($_FILES['img_paths']['name'][0])) {
        $uploadDir   = __DIR__ . '/../product/images/';
        $webBasePath = '/F&LGlamCo/product/images/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        foreach ($_FILES['img_paths']['name'] as $idx => $file) {
            if ($_FILES['img_paths']['error'][$idx] !== UPLOAD_ERR_OK) continue;
            $orig = basename($file);
            $uniq = time().'_'.bin2hex(random_bytes(4)).'_'.preg_replace('/[^A-Za-z0-9._-]/','_',$orig);
            $targetFs  = $uploadDir.$uniq;
            $targetWeb = $webBasePath.$uniq;
            if (move_uploaded_file($_FILES['img_paths']['tmp_name'][$idx], $targetFs)) {
                $sqlImg = "INSERT INTO product_images (item_id, filename) VALUES (?, ?)";
                $stmtImg = mysqli_prepare($conn, $sqlImg);
                mysqli_stmt_bind_param($stmtImg, "is", $itemId, $targetWeb);
                mysqli_stmt_execute($stmtImg);
                mysqli_stmt_close($stmtImg);
            }
        }
    }

    $conn->commit();
    header("Location: index.php?msg=Item+created+successfully");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    die("Transaction failed: " . htmlspecialchars($e->getMessage()));
}
?>
