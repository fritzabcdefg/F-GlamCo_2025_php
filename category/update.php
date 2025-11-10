<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/config.php');
require_once __DIR__ . '/../includes/flash.php';

if (isset($_POST['submit'])) {
	$id = intval($_POST['category_id']);
	$name = trim($_POST['name']);
	$description = trim($_POST['description']);

	if ($name === '') {
		$_SESSION['cat_name_error'] = 'Please enter a category name.';
		header("Location: edit.php?id={$id}");
		exit();
	}

	require_once __DIR__ . '/../includes/csrf.php';

	// CSRF
	if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || !csrf_verify($_POST['csrf_token'])) {
		flash_set('Invalid request.', 'danger');
		header('Location: index.php');
		exit;
	}

	$upd = mysqli_prepare($conn, "UPDATE categories SET name = ?, description = ? WHERE category_id = ?");
	if ($upd) {
		mysqli_stmt_bind_param($upd, 'ssi', $name, $description, $id);
		$res = mysqli_stmt_execute($upd);
		mysqli_stmt_close($upd);
	} else {
		$res = false;
	}
	if ($res) {
		header('Location: index.php');
		exit();
	} else {
		echo 'Error: ' . mysqli_error($conn);
	}
}
