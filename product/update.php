<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/config.php');

if (isset($_POST['submit'])) {
    $item_id  = intval($_POST['item_id']);
    $name     = trim($_POST['name']);
    $cost     = floatval($_POST['cost_price']);
    $sell     = floatval($_POST['sell_price']);
    $qty      = intval($_POST['quantity']);
    $supplier = trim($_POST['supplier_name']);
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;

    if ($name === '') {
        header("Location: edit.php?id={$item_id}");
        exit();
    }

    // --- Handle newly uploaded images ---
    $uploadedFiles = [];
    if (isset($_FILES['img_paths'])) {
        $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        for ($i = 0; $i < count($_FILES['img_paths']['name']); $i++) {
            if ($_FILES['img_paths']['error'][$i] !== UPLOAD_ERR_OK) continue;
            if (!in_array($_FILES['img_paths']['type'][$i], $allowed)) continue;

            $tmp  = $_FILES['img_paths']['tmp_name'][$i];
            $orig = basename($_FILES['img_paths']['name'][$i]);
            $uniq = time() . '_' . bin2hex(random_bytes(4)) . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $orig);
            $targetPath = 'images/' . $uniq;

            if (move_uploaded_file($tmp, $targetPath)) {
                $uploadedFiles[] = $targetPath;
            }
        }
    }

    // --- Decide main image (always update with first uploaded if available) ---
    $mainImg = '';
    if (!empty($uploadedFiles)) {
        $mainImg = $uploadedFiles[0];
    } else {
        // keep existing if no new upload
        $selStmt = mysqli_prepare($conn, "SELECT img_path FROM items WHERE item_id = ? LIMIT 1");
        mysqli_stmt_bind_param($selStmt, 'i', $item_id);
        mysqli_stmt_execute($selStmt);
        $resSel = mysqli_stmt_get_result($selStmt);
        if ($resSel && mysqli_num_rows($resSel) > 0) {
            $r = mysqli_fetch_assoc($resSel);
            $mainImg = $r['img_path'] ?? '';
        }
        mysqli_stmt_close($selStmt);
    }

    // --- Update items ---
    if ($category_id === null) {
        $upd = mysqli_prepare($conn, "UPDATE items 
            SET name = ?, cost_price = ?, sell_price = ?, supplier_name = ?, img_path = ?, category_id = NULL 
            WHERE item_id = ?");
        mysqli_stmt_bind_param($upd, 'sddssi', $name, $cost, $sell, $supplier, $mainImg, $item_id);
    } else {
        $upd = mysqli_prepare($conn, "UPDATE items 
            SET name = ?, cost_price = ?, sell_price = ?, supplier_name = ?, img_path = ?, category_id = ? 
            WHERE item_id = ?");
        mysqli_stmt_bind_param($upd, 'sddssii', $name, $cost, $sell, $supplier, $mainImg, $category_id, $item_id);
    }
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);

    // --- Insert additional images into product_images ---
    if (!empty($uploadedFiles)) {
        $insImg = mysqli_prepare($conn, "INSERT INTO product_images (item_id, filename) VALUES (?, ?)");
        foreach ($uploadedFiles as $idx => $f) {
            if ($idx == 0) continue; // skip first (main image already in items.img_path)
            mysqli_stmt_bind_param($insImg, 'is', $item_id, $f);
            mysqli_stmt_execute($insImg);
        }
        mysqli_stmt_close($insImg);
    }

    // --- Update stock ---
    $q = mysqli_prepare($conn, "SELECT 1 FROM stocks WHERE item_id = ? LIMIT 1");
    mysqli_stmt_bind_param($q, 'i', $item_id);
    mysqli_stmt_execute($q);
    $resQ = mysqli_stmt_get_result($q);
    $exists = ($resQ && mysqli_num_rows($resQ) > 0);
    mysqli_stmt_close($q);

    if ($exists) {
        $u = mysqli_prepare($conn, "UPDATE stocks SET quantity = ? WHERE item_id = ?");
        mysqli_stmt_bind_param($u, 'ii', $qty, $item_id);
        mysqli_stmt_execute($u);
        mysqli_stmt_close($u);
    } else {
        $insS = mysqli_prepare($conn, "INSERT INTO stocks (item_id, quantity) VALUES (?, ?)");
        mysqli_stmt_bind_param($insS, 'ii', $item_id, $qty);
        mysqli_stmt_execute($insS);
        mysqli_stmt_close($insS);
    }

    header('Location: index.php');
    exit();
}
?>
