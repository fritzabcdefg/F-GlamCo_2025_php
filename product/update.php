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
        header('Location: edit.php?id=' . (isset($_POST['item_id']) ? intval($_POST['item_id']) : ''));
        exit;
    }
}

if (isset($_POST['submit'])) {
    $item_id = intval($_POST['item_id']);
    $name = trim($_POST['name']);
    $cost = trim($_POST['cost_price']);
    $sell = trim($_POST['sell_price']);
    $qty = intval($_POST['quantity']);
    $supplier = trim($_POST['supplier_name']);

    if ($name === '') {
        flash_set('Name is required', 'danger');
        header("Location: edit.php?id={$item_id}");
        exit();
    }

    // get existing img path (prepared)
    $existingImg = '';
    $selStmt = mysqli_prepare($conn, "SELECT img_path FROM items WHERE item_id = ? LIMIT 1");
    if ($selStmt) {
        mysqli_stmt_bind_param($selStmt, 'i', $item_id);
        mysqli_stmt_execute($selStmt);
        $resSel = mysqli_stmt_get_result($selStmt);
        if ($resSel && mysqli_num_rows($resSel) > 0) {
            $r = mysqli_fetch_assoc($resSel);
            $existingImg = $r['img_path'] ?? '';
        }
        mysqli_stmt_close($selStmt);
    }

    // handle newly uploaded images (add to gallery)
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
            $uniq = time() . '_' . bin2hex(random_bytes(4)) . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $orig);
            $targetPath = 'images/' . $uniq;
            if (move_uploaded_file($tmp, $targetPath)) {
                $uploadedFiles[] = $targetPath;
            }
        }
    }

    // process deletions from gallery
    if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
            foreach ($_POST['delete_images'] as $delId) {
            $delId = intval($delId);
            $sel = mysqli_prepare($conn, "SELECT filename FROM product_images WHERE id = ? AND item_id = ? LIMIT 1");
            if ($sel) {
                mysqli_stmt_bind_param($sel, 'ii', $delId, $item_id);
                mysqli_stmt_execute($sel);
                $resSel = mysqli_stmt_get_result($sel);
                if ($resSel && mysqli_num_rows($resSel) > 0) {
                    $r = mysqli_fetch_assoc($resSel);
                    $fname = $r['filename'];
                    if ($fname && file_exists($fname)) {
                        @unlink($fname);
                    }
                    mysqli_stmt_close($sel);
                    $delStmt = mysqli_prepare($conn, "DELETE FROM product_images WHERE id = ?");
                    if ($delStmt) {
                        mysqli_stmt_bind_param($delStmt, 'i', $delId);
                        mysqli_stmt_execute($delStmt);
                        mysqli_stmt_close($delStmt);
                    }
                } else {
                    mysqli_stmt_close($sel);
                }
            }
        }
    }

    $nameEsc = mysqli_real_escape_string($conn, $name);
    $costEsc = mysqli_real_escape_string($conn, $cost);
    $sellEsc = mysqli_real_escape_string($conn, $sell);
    $supplierEsc = mysqli_real_escape_string($conn, $supplier);
    // if there is no main img set but we have uploaded images, set first as main
    $target = $existingImg;
    if (empty($target) && count($uploadedFiles)) {
        $target = $uploadedFiles[0];
    }
    $targetEsc = mysqli_real_escape_string($conn, $target);

    $category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? intval($_POST['category_id']) : 'NULL';

    // update items using prepared statement (handle NULL category)
    if ($category_id === 'NULL') {
        $upd = mysqli_prepare($conn, "UPDATE items SET name = ?, cost_price = ?, sell_price = ?, supplier_name = ?, img_path = ?, category_id = NULL WHERE item_id = ?");
        if ($upd) {
            mysqli_stmt_bind_param($upd, 'sddssi', $name, $cost, $sell, $supplier, $target, $item_id);
            mysqli_stmt_execute($upd);
            mysqli_stmt_close($upd);
        }
    } else {
        $catVal = intval($category_id);
        $upd = mysqli_prepare($conn, "UPDATE items SET name = ?, cost_price = ?, sell_price = ?, supplier_name = ?, img_path = ?, category_id = ? WHERE item_id = ?");
        if ($upd) {
            mysqli_stmt_bind_param($upd, 'sddssii', $name, $cost, $sell, $supplier, $target, $catVal, $item_id);
            mysqli_stmt_execute($upd);
            mysqli_stmt_close($upd);
        }
    }

    // insert any newly uploaded images into product_images
    if (!empty($uploadedFiles)) {
        $insImg = mysqli_prepare($conn, "INSERT INTO product_images (item_id, filename) VALUES (?, ?)");
        if ($insImg) {
            foreach ($uploadedFiles as $f) {
                $fEsc = $f; // prepared statement will handle value
                mysqli_stmt_bind_param($insImg, 'is', $item_id, $fEsc);
                mysqli_stmt_execute($insImg);
            }
            mysqli_stmt_close($insImg);
        }
    }

    // update stock
    $q = mysqli_prepare($conn, "SELECT 1 FROM stocks WHERE item_id = ? LIMIT 1");
    if ($q) {
        mysqli_stmt_bind_param($q, 'i', $item_id);
        mysqli_stmt_execute($q);
        $resQ = mysqli_stmt_get_result($q);
        $exists = ($resQ && mysqli_num_rows($resQ) > 0);
        mysqli_stmt_close($q);

        if ($exists) {
            $u = mysqli_prepare($conn, "UPDATE stocks SET quantity = ? WHERE item_id = ?");
            if ($u) {
                mysqli_stmt_bind_param($u, 'ii', $qty, $item_id);
                mysqli_stmt_execute($u);
                mysqli_stmt_close($u);
            }
        } else {
            $insS = mysqli_prepare($conn, "INSERT INTO stocks (item_id, quantity) VALUES (?, ?)");
            if ($insS) {
                mysqli_stmt_bind_param($insS, 'ii', $item_id, $qty);
                mysqli_stmt_execute($insS);
                mysqli_stmt_close($insS);
            }
        }
    }

    header('Location: index.php');
    exit();
}