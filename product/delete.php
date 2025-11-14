<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/config.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id) {
    // fetch image path (prepared)
    $img = '';
    $sel = mysqli_prepare($conn, "SELECT img_path FROM items WHERE item_id = ? LIMIT 1");
    if ($sel) {
        mysqli_stmt_bind_param($sel, 'i', $id);
        mysqli_stmt_execute($sel);
        $resSel = mysqli_stmt_get_result($sel);
        if ($resSel && mysqli_num_rows($resSel) > 0) {
            $r = mysqli_fetch_assoc($resSel);
            $img = $r['img_path'] ?? '';
        }
        mysqli_stmt_close($sel);
    }

    // delete stock first (if present)
    $delS = mysqli_prepare($conn, "DELETE FROM stocks WHERE item_id = ?");
    if ($delS) {
        mysqli_stmt_bind_param($delS, 'i', $id);
        mysqli_stmt_execute($delS);
        mysqli_stmt_close($delS);
    }

    // delete product_images rows and unlink files (select then delete)
    $imgQ = mysqli_prepare($conn, "SELECT filename FROM product_images WHERE item_id = ?");
    if ($imgQ) {
        mysqli_stmt_bind_param($imgQ, 'i', $id);
        mysqli_stmt_execute($imgQ);
        $resImgs = mysqli_stmt_get_result($imgQ);
        if ($resImgs) {
            while ($r = mysqli_fetch_assoc($resImgs)) {
                $f = $r['filename'];
                if ($f && file_exists($f)) {
                    @unlink($f);
                }
            }
        }
        mysqli_stmt_close($imgQ);

        $delImgs = mysqli_prepare($conn, "DELETE FROM product_images WHERE item_id = ?");
        if ($delImgs) {
            mysqli_stmt_bind_param($delImgs, 'i', $id);
            mysqli_stmt_execute($delImgs);
            mysqli_stmt_close($delImgs);
        }
    }

    // delete item
    $delItem = mysqli_prepare($conn, "DELETE FROM items WHERE item_id = ?");
    if ($delItem) {
        mysqli_stmt_bind_param($delItem, 'i', $id);
        mysqli_stmt_execute($delItem);
        mysqli_stmt_close($delItem);
    }

    // delete main image file if it exists
    if ($img && file_exists($img)) {
        @unlink($img);
    }
}

header('Location: index.php');
exit();
