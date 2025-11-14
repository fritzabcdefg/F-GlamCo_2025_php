<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/config.php');

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    // keep values for repopulation on error
    $_SESSION['cat_name'] = $name;
    $_SESSION['cat_description'] = $description;

    if ($name === '') {
        $_SESSION['cat_name_error'] = 'Please enter a category name.';
        header('Location: create.php');
        exit();
    }

    $ins = mysqli_prepare($conn, "INSERT INTO categories (name, description) VALUES (?, ?)");
    if ($ins) {
        mysqli_stmt_bind_param($ins, 'ss', $name, $description);
        $res = mysqli_stmt_execute($ins);
        mysqli_stmt_close($ins);
    } else {
        $res = false;
    }

    if ($res) {
        // clear session-backed form values
        unset($_SESSION['cat_name'], $_SESSION['cat_description']);
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['cat_name_error'] = 'Failed to create category.';
        header('Location: create.php');
        exit();
    }
}
