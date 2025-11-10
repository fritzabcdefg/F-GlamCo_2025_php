<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/config.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id) {
    $sel = mysqli_prepare($conn, "SELECT is_visible FROM reviews WHERE id = ? LIMIT 1");
    if ($sel) {
        mysqli_stmt_bind_param($sel, 'i', $id);
        mysqli_stmt_execute($sel);
        $res = mysqli_stmt_get_result($sel);
        if ($res && mysqli_num_rows($res) > 0) {
            $r = mysqli_fetch_assoc($res);
            $new = $r['is_visible'] ? 0 : 1;
            mysqli_stmt_close($sel);
            $upd = mysqli_prepare($conn, "UPDATE reviews SET is_visible = ? WHERE id = ?");
            if ($upd) {
                mysqli_stmt_bind_param($upd, 'ii', $new, $id);
                mysqli_stmt_execute($upd);
                mysqli_stmt_close($upd);
            }
        } else {
            mysqli_stmt_close($sel);
        }
    }
}
header('Location: reviews.php');
exit();
?>