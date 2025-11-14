<?php
session_start();
include("../includes/config.php");
include("../includes/header.php");

// basic server-side validation
$email       = isset($_POST['email']) ? trim($_POST['email']) : '';
$password    = isset($_POST['password']) ? trim($_POST['password']) : '';
$confirmPass = isset($_POST['confirmPass']) ? trim($_POST['confirmPass']) : '';

if ($email === '' || $password === '' || $confirmPass === '') {
    header("Location: register.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: register.php");
    exit();
}

if ($password !== $confirmPass) {
    header("Location: register.php");
    exit();
}

if (strlen($password) < 8) {
    header("Location: register.php");
    exit();
}

// check for existing email
$chk = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($chk, 's', $email);
mysqli_stmt_execute($chk);
mysqli_stmt_store_result($chk);
if (mysqli_stmt_num_rows($chk) > 0) {
    header("Location: register.php");
    exit();
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);
$created_at    = date('Y-m-d H:i:s');

$sql  = "INSERT INTO users (email,password,created_at) VALUES(?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sss", $email, $password_hash, $created_at);
$result = mysqli_stmt_execute($stmt);

if ($result) {
    header("Location: login.php");
    exit();
} else {
    header("Location: register.php");
    exit();
}
