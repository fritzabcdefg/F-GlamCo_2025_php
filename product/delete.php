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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    if ($id > 0) {
        $conn->begin_transaction();

        $delS = mysqli_prepare($conn, "DELETE FROM stocks WHERE item_id=?");
        if ($delS) {
            mysqli_stmt_bind_param($delS, 'i', $id);
            mysqli_stmt_execute($delS);
            mysqli_stmt_close($delS);
        }

        $imgQ = mysqli_prepare($conn, "SELECT filename FROM product_images WHERE item_id=?");
        if ($imgQ) {
            mysqli_stmt_bind_param($imgQ, 'i', $id);
            mysqli_stmt_execute($imgQ);
            $resImgs = mysqli_stmt_get_result($imgQ);
            if ($resImgs) {
                while ($r = mysqli_fetch_assoc($resImgs)) {
                    $f = $r['filename'];
                    if ($f && file_exists(__DIR__ . '/..' . $f)) {
                        @unlink(__DIR__ . '/..' . $f);
                    }
                }
            }
            mysqli_stmt_close($imgQ);
        }

        $delImgs = mysqli_prepare($conn, "DELETE FROM product_images WHERE item_id=?");
        if ($delImgs) {
            mysqli_stmt_bind_param($delImgs, 'i', $id);
            mysqli_stmt_execute($delImgs);
            mysqli_stmt_close($delImgs);
        }

        $delItem = mysqli_prepare($conn, "DELETE FROM items WHERE item_id=?");
        if ($delItem) {
            mysqli_stmt_bind_param($delItem, 'i', $id);
            mysqli_stmt_execute($delItem);
            mysqli_stmt_close($delItem);
        }

        $conn->commit();
    }
} catch (Exception $e) {
    $conn->rollback();
}

header('Location: index.php');
exit();
?>
