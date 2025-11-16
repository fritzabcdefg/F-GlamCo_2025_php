<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/config.php');

if (isset($_POST['submit'])) {
    $item_id     = intval($_POST['item_id']);
    $name        = trim($_POST['name']);
    $qty         = intval($_POST['quantity']);
    $supplier    = trim($_POST['supplier_name']);
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;

    // ✅ Normalize prices to 2 decimals
    $costRaw = isset($_POST['cost_price']) ? (float)$_POST['cost_price'] : 0;
    $sellRaw = isset($_POST['sell_price']) ? (float)$_POST['sell_price'] : 0;
    $cost    = number_format(round($costRaw, 2), 2, '.', '');
    $sell    = number_format(round($sellRaw, 2), 2, '.', '');

    if ($name === '') {
        header("Location: edit.php?id={$item_id}");
        exit();
    }

    // --- Handle newly uploaded images ---
    $uploadedFiles = [];
    if (isset($_FILES['img_paths'])) {
        $allowed     = ['image/jpeg','image/jpg','image/png','image/gif'];
        $uploadDir   = __DIR__ . '/../product/images/';
        $webBasePath = '/F&LGlamCo/product/images/';
        if (!is_dir($uploadDir)) mkdir($uploadDir,0777,true);

        for ($i=0; $i<count($_FILES['img_paths']['name']); $i++) {
            if ($_FILES['img_paths']['error'][$i] !== UPLOAD_ERR_OK) continue;
            if (!in_array($_FILES['img_paths']['type'][$i], $allowed)) continue;

            $orig = basename($_FILES['img_paths']['name'][$i]);
            $uniq = time().'_'.bin2hex(random_bytes(4)).'_'.preg_replace('/[^A-Za-z0-9._-]/','_',$orig);
            $targetFs  = $uploadDir.$uniq;
            $targetWeb = $webBasePath.$uniq;

            if (move_uploaded_file($_FILES['img_paths']['tmp_name'][$i], $targetFs)) {
                $uploadedFiles[] = $targetWeb;
            }
        }
    }

    // --- Process deletions from gallery ---
    if (!empty($_POST['delete_images']) && is_array($_POST['delete_images'])) {
        foreach ($_POST['delete_images'] as $delId) {
            $delId = intval($delId);
            $sel = mysqli_prepare($conn,"SELECT filename FROM product_images WHERE id=? AND item_id=? LIMIT 1");
            mysqli_stmt_bind_param($sel,'ii',$delId,$item_id);
            mysqli_stmt_execute($sel);
            $resSel = mysqli_stmt_get_result($sel);
            if ($resSel && mysqli_num_rows($resSel)>0) {
                $r = mysqli_fetch_assoc($resSel);
                $fname = $r['filename'];
                if ($fname && file_exists(__DIR__.'/..'.$fname)) {
                    @unlink(__DIR__.'/..'.$fname);
                }
            }
            mysqli_stmt_close($sel);

            $delStmt = mysqli_prepare($conn,"DELETE FROM product_images WHERE id=?");
            mysqli_stmt_bind_param($delStmt,'i',$delId);
            mysqli_stmt_execute($delStmt);
            mysqli_stmt_close($delStmt);
        }
    }

    // --- Update items ---
    if ($category_id === null) {
        $upd = mysqli_prepare($conn,"UPDATE items SET name=?, cost_price=?, sell_price=?, supplier_name=?, category_id=NULL WHERE item_id=?");
        mysqli_stmt_bind_param($upd,'sddsi',$name,$cost,$sell,$supplier,$item_id);
    } else {
        $upd = mysqli_prepare($conn,"UPDATE items SET name=?, cost_price=?, sell_price=?, supplier_name=?, category_id=? WHERE item_id=?");
        mysqli_stmt_bind_param($upd,'sddsii',$name,$cost,$sell,$supplier,$category_id,$item_id);
    }
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);

    // --- Insert any newly uploaded images ---
    if (!empty($uploadedFiles)) {
        $insImg = mysqli_prepare($conn,"INSERT INTO product_images (item_id, filename) VALUES (?, ?)");
        foreach ($uploadedFiles as $f) {
            mysqli_stmt_bind_param($insImg,'is',$item_id,$f);
            mysqli_stmt_execute($insImg);
        }
        mysqli_stmt_close($insImg);
    }

    // --- Update stock ---
    $q = mysqli_prepare($conn,"SELECT 1 FROM stocks WHERE item_id=? LIMIT 1");
    mysqli_stmt_bind_param($q,'i',$item_id);
    mysqli_stmt_execute($q);
    $resQ = mysqli_stmt_get_result($q);
    $exists = ($resQ && mysqli_num_rows($resQ)>0);
    mysqli_stmt_close($q);

    if ($exists) {
        $u = mysqli_prepare($conn,"UPDATE stocks SET quantity=? WHERE item_id=?");
        mysqli_stmt_bind_param($u,'ii',$qty,$item_id);
        mysqli_stmt_execute($u);
        mysqli_stmt_close($u);
    } else {
        $insS = mysqli_prepare($conn,"INSERT INTO stocks (item_id, quantity) VALUES (?, ?)");
        mysqli_stmt_bind_param($insS,'ii',$item_id,$qty);
        mysqli_stmt_execute($insS);
        mysqli_stmt_close($insS);
    }

    header('Location: index.php?msg=Item+updated+successfully');
    exit();
}
?>
