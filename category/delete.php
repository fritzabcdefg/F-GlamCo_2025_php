<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/config.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    if ($id > 0) {
        $conn->begin_transaction();

        $del = mysqli_prepare($conn, "DELETE FROM categories WHERE category_id = ?");
        if ($del) {
            mysqli_stmt_bind_param($del, 'i', $id);
            if (!mysqli_stmt_execute($del)) {
                throw new Exception("Failed to delete category");
            }
            mysqli_stmt_close($del);
        } else {
            throw new Exception("Failed to prepare statement");
        }

        $conn->commit();
    }
} catch (Exception $e) {
    $conn->rollback();
}

header('Location: index.php');
exit();
