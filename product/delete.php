<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/config.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id) {
    // delete stock first
    $delS = mysqli_prepare($conn,"DELETE FROM stocks WHERE item_id=?");
    mysqli_stmt_bind_param($delS,'i',$id);
    mysqli_stmt_execute($delS);
    mysqli_stmt_close($delS);

    // delete product_images rows and unlink files
    $imgQ = mysqli_prepare($conn,"SELECT filename FROM product_images WHERE item_id=?");
    mysqli_stmt_bind_param($imgQ,'i',$id);
    mysqli_stmt_execute($imgQ);
    $resImgs = mysqli_stmt_get_result($imgQ);
    if ($resImgs) {
        while ($r = mysqli_fetch_assoc($resImgs)) {
            $f = $r['filename'];
            if ($f && file_exists(__DIR__.'/..'.$f)) {
                @unlink(__DIR__.'/..'.$f);
            }
        }
    }
    mysqli_stmt_close($imgQ);

    $delImgs = mysqli_prepare($conn,"DELETE FROM product_images WHERE item_id=?");
    mysqli_stmt_bind_param($delImgs,'i',$id);
    mysqli_stmt_execute($delImgs);
    mysqli_stmt_close($delImgs);

    // delete item
    $delItem = mysqli_prepare($conn,"DELETE FROM items WHERE item_id=?");
    mysqli_stmt_bind_param($delItem,'i',$id);
    mysqli_stmt_execute($delItem);
    mysqli_stmt_close($delItem);
}

header('Location: index.php');
exit();
?>
