<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/config.php');

if (isset($_POST['submit'])) {
    $name        = trim($_POST['name']);
    $category_id = intval($_POST['category_id']);
    $cost_price  = floatval($_POST['cost_price']);
    $sell_price  = floatval($_POST['sell_price']);
    $quantity    = intval($_POST['quantity']);
    $supplier    = trim($_POST['supplier_name'] ?? 'Default Supplier');

    // Directories
    $uploadDir   = __DIR__ . '/../product/images/';   // filesystem path
    $webBasePath = '/F&LGlamCo/product/images/';      // web path for <img src>

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Handle image upload (first image = main image)
    $img_path = '';
    $uploadedFiles = [];
    if (!empty($_FILES['img_paths']['name'][0])) {
        foreach ($_FILES['img_paths']['name'] as $idx => $file) {
            if ($_FILES['img_paths']['error'][$idx] !== UPLOAD_ERR_OK) continue;

            $orig = basename($file);
            $uniq = time() . '_' . bin2hex(random_bytes(4)) . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $orig);
            $targetFs  = $uploadDir . $uniq;     // filesystem
            $targetWeb = $webBasePath . $uniq;   // web path

            if (move_uploaded_file($_FILES['img_paths']['tmp_name'][$idx], $targetFs)) {
                $uploadedFiles[] = $targetWeb;
            }
        }
    }

    if (!empty($uploadedFiles)) {
        $img_path = $uploadedFiles[0]; // first image as main
    }

    // Insert into items
    $sql = "INSERT INTO items (name, cost_price, sell_price, supplier_name, img_path, category_id)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sdsssi", $name, $cost_price, $sell_price, $supplier, $img_path, $category_id);
    $ok = mysqli_stmt_execute($stmt);
    $itemId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    if ($ok && $itemId) {
        // Insert stock quantity
        $sqlStock = "INSERT INTO stocks (item_id, quantity) VALUES (?, ?)";
        $stmtStock = mysqli_prepare($conn, $sqlStock);
        mysqli_stmt_bind_param($stmtStock, "ii", $itemId, $quantity);
        mysqli_stmt_execute($stmtStock);
        mysqli_stmt_close($stmtStock);

        // Insert extra images into product_images
        if (count($uploadedFiles) > 1) {
            $sqlImg = "INSERT INTO product_images (item_id, filename) VALUES (?, ?)";
            $stmtImg = mysqli_prepare($conn, $sqlImg);
            foreach ($uploadedFiles as $idx => $f) {
                if ($idx == 0) continue; // skip first
                mysqli_stmt_bind_param($stmtImg, "is", $itemId, $f);
                mysqli_stmt_execute($stmtImg);
            }
            mysqli_stmt_close($stmtImg);
        }

        header("Location: edit.php?id=" . $itemId);
        exit;
    } else {
        echo "Error inserting item: " . mysqli_error($conn);
    }
}
