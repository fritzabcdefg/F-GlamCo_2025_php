<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/config.php');
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';

// CSRF check
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !csrf_verify($_POST['csrf_token'])) {
        flash_set('Invalid form submission.', 'danger');
        header('Location: create.php');
        exit;
    }
}
$_SESSION['name'] = trim($_POST['name']);
$_SESSION['cost'] = trim($_POST['cost_price']);
$_SESSION['sell'] = trim($_POST['sell_price']);
$_SESSION['qty'] = $_POST['quantity'];
$_SESSION['category_id'] = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;


if (isset($_POST['submit'])) {
    $cost =  trim($_POST['cost_price']);
    $sell = trim($_POST['sell_price']);
    $name = trim($_POST['name']);
    $qty = $_POST['quantity'];

    if (empty($_POST['name'])) {
        $_SESSION['nameError'] = 'Please input a Product name';
        header("Location: create.php");
    }

    if (empty($_POST['cost_price']) || (! is_numeric($cost))) {
        $_SESSION['costError'] = 'error product price format';
        header("Location: create.php");
    }

    if (empty($_POST['sell_price']) || (! is_numeric($sell))) {
        $_SESSION['sellError'] = 'error product price format';
        header("Location: create.php");
    }
    // handle multiple uploaded images (input name img_paths[])
    $uploadedFiles = [];
    if (isset($_FILES['img_paths'])) {
        $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        for ($i = 0; $i < count($_FILES['img_paths']['name']); $i++) {
            $err = $_FILES['img_paths']['error'][$i];
            if ($err !== UPLOAD_ERR_OK) continue;
            $type = $_FILES['img_paths']['type'][$i];
            if (!in_array($type, $allowed)) continue;
            $tmp = $_FILES['img_paths']['tmp_name'][$i];
            $orig = basename($_FILES['img_paths']['name'][$i]);
            // make filename unique
            $uniq = time() . '_' . bin2hex(random_bytes(4)) . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $orig);
            $targetPath = 'images/' . $uniq;
            if (move_uploaded_file($tmp, $targetPath)) {
                $uploadedFiles[] = $targetPath;
            }
        }
    }

    // previous single-file fallback (if developer used old field name)
    if (empty($uploadedFiles) && isset($_FILES['img_path']) && $_FILES['img_path']['error'] == UPLOAD_ERR_OK) {
        $tmp = $_FILES['img_path']['tmp_name'];
        $orig = basename($_FILES['img_path']['name']);
        $uniq = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $orig);
        $targetPath = 'images/' . $uniq;
        if (move_uploaded_file($tmp, $targetPath)) {
            $uploadedFiles[] = $targetPath;
        }
    }

    $category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? intval($_POST['category_id']) : 'NULL';

    // use first uploaded image as main thumbnail (legacy img_path)
    $mainImg = count($uploadedFiles) ? $uploadedFiles[0] : '';

    // insert item (handle nullable category_id)
    if ($category_id === 'NULL') {
        $ins = mysqli_prepare($conn, "INSERT INTO items (name, category_id, cost_price, sell_price, img_path) VALUES (?, NULL, ?, ?, ?)");
        if ($ins) {
            mysqli_stmt_bind_param($ins, 'sdds', $name, $cost, $sell, $mainImg);
            mysqli_stmt_execute($ins);
            mysqli_stmt_close($ins);
        }
    } else {
        $catVal = intval($category_id);
        $ins = mysqli_prepare($conn, "INSERT INTO items (name, category_id, cost_price, sell_price, img_path) VALUES (?, ?, ?, ?, ?)");
        if ($ins) {
            mysqli_stmt_bind_param($ins, 'sidds', $name, $catVal, $cost, $sell, $mainImg);
            mysqli_stmt_execute($ins);
            mysqli_stmt_close($ins);
        }
    }

    $item_id = mysqli_insert_id($conn);
    if ($item_id) {
        // insert uploaded images into product_images
        if (!empty($uploadedFiles)) {
            $insImg = mysqli_prepare($conn, "INSERT INTO product_images (item_id, filename) VALUES (?, ?)");
            if ($insImg) {
                foreach ($uploadedFiles as $f) {
                    mysqli_stmt_bind_param($insImg, 'is', $item_id, $f);
                    mysqli_stmt_execute($insImg);
                }
                mysqli_stmt_close($insImg);
            }
        }

        $insS = mysqli_prepare($conn, "INSERT INTO stocks(item_id, quantity) VALUES(?, ?)");
        if ($insS) {
            mysqli_stmt_bind_param($insS, 'ii', $item_id, $qty);
            mysqli_stmt_execute($insS);
            mysqli_stmt_close($insS);
            header("Location: index.php");
            exit();
        }
    }
}