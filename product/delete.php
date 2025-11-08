<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/config.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id) {
    // fetch image path
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT img_path FROM items WHERE item_id = {$id} LIMIT 1"));
    $img = $row['img_path'] ?? '';

    // delete stock first (if present)
    mysqli_query($conn, "DELETE FROM stocks WHERE item_id = {$id}");

    // delete item
    mysqli_query($conn, "DELETE FROM items WHERE item_id = {$id}");

    // delete image file if it exists in images/
    if ($img && file_exists($img)) {
        @unlink($img);
    }
}

header('Location: index.php');
exit();
