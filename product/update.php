<?php
session_start();
include('../includes/config.php');

if (isset($_POST['submit'])) {
    $item_id = intval($_POST['item_id']);
    $name = trim($_POST['name']);
    $cost = trim($_POST['cost_price']);
    $sell = trim($_POST['sell_price']);
    $qty = intval($_POST['quantity']);
    $supplier = trim($_POST['supplier_name']);

    if ($name === '') {
        $_SESSION['message'] = 'Name is required';
        header("Location: edit.php?id={$item_id}");
        exit();
    }

    // get existing img path
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT img_path FROM items WHERE item_id = {$item_id} LIMIT 1"));
    $existingImg = $row['img_path'] ?? '';

    $target = $existingImg;
    if (isset($_FILES['img_path']) && $_FILES['img_path']['error'] == UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg','image/jpg','image/png'];
        if (in_array($_FILES['img_path']['type'], $allowed)) {
            $source = $_FILES['img_path']['tmp_name'];
            $filename = basename($_FILES['img_path']['name']);
            $targetPath = 'images/' . $filename;
            if (move_uploaded_file($source, $targetPath)) {
                $target = $targetPath;
                // optionally remove old image if different and exists
                if ($existingImg && $existingImg !== $target && file_exists($existingImg)) {
                    @unlink($existingImg);
                }
            }
        }
    }

    $nameEsc = mysqli_real_escape_string($conn, $name);
    $costEsc = mysqli_real_escape_string($conn, $cost);
    $sellEsc = mysqli_real_escape_string($conn, $sell);
    $supplierEsc = mysqli_real_escape_string($conn, $supplier);
    $targetEsc = mysqli_real_escape_string($conn, $target);

    $category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? intval($_POST['category_id']) : 'NULL';

    $sql = "UPDATE items SET name = '{$nameEsc}', cost_price = '{$costEsc}', sell_price = '{$sellEsc}', supplier_name = '{$supplierEsc}', img_path = '{$targetEsc}', category_id = {$category_id} WHERE item_id = {$item_id}";
    mysqli_query($conn, $sql);

    // update stock
    $q = "SELECT * FROM stocks WHERE item_id = {$item_id} LIMIT 1";
    $r = mysqli_query($conn, $q);
    if ($r && mysqli_num_rows($r) > 0) {
        mysqli_query($conn, "UPDATE stocks SET quantity = {$qty} WHERE item_id = {$item_id}");
    } else {
        mysqli_query($conn, "INSERT INTO stocks (item_id, quantity) VALUES ({$item_id}, {$qty})");
    }

    header('Location: index.php');
    exit();
}