<?php
session_start();
include("../includes/config.php");
include("../includes/header.php");

$email = trim($_POST['email']);
$password = trim($_POST['password']);
$confirmPass = trim($_POST['confirmPass']);

if ($password !== $confirmPass) {
    $_SESSION['message'] = 'passwords do not match';
    header("Location: register.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['message'] = 'invalid email format';
    header("Location: register.php");
    exit();
}

$password = sha1($password); 
$created_at = date('Y-m-d H:i:s');

$sql = "INSERT INTO users (email,password,created_at) VALUES(?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sss", $email, $password, $created_at);
$result = mysqli_stmt_execute($stmt);

if ($result) {
    $_SESSION['user_id'] = mysqli_insert_id($conn);
    header("Location: login.php");
    exit();
} else {
    $_SESSION['message'] = 'registration failed';
    header("Location: register.php");
    exit();
}
