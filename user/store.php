<?php
session_start();
include("../includes/config.php");
include("../includes/header.php");
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';

// basic server-side validation
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$confirmPass = isset($_POST['confirmPass']) ? trim($_POST['confirmPass']) : '';

// CSRF
if (!isset($_POST['csrf_token']) || !csrf_verify($_POST['csrf_token'])) {
    flash_set('Invalid form submission.', 'danger');
    header("Location: register.php");
    exit();
}

if ($email === '' || $password === '' || $confirmPass === '') {
    flash_set('All fields are required.', 'danger');
    header("Location: register.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_set('Please enter a valid email address.', 'danger');
    header("Location: register.php");
    exit();
}

if ($password !== $confirmPass) {
    flash_set('Passwords do not match.', 'danger');
    header("Location: register.php");
    exit();
}

if (strlen($password) < 8) {
    flash_set('Password must be at least 8 characters long.', 'danger');
    header("Location: register.php");
    exit();
}

// check for existing email
$chk = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($chk, 's', $email);
mysqli_stmt_execute($chk);
mysqli_stmt_store_result($chk);
if (mysqli_stmt_num_rows($chk) > 0) {
    flash_set('That email is already registered. Please login or use a different email.', 'danger');
    header("Location: register.php");
    exit();
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);
$created_at = date('Y-m-d H:i:s');

$sql = "INSERT INTO users (email,password,created_at) VALUES(?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sss", $email, $password_hash, $created_at);
$result = mysqli_stmt_execute($stmt);

if ($result) {
    flash_set('Registration successful. You can now login.', 'success');
    header("Location: login.php");
    exit();
} else {
    flash_set('Registration failed. Please try again later.', 'danger');
    header("Location: register.php");
    exit();
}
