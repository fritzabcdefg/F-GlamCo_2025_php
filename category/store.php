<?php
session_start();
	include('../includes/auth_admin.php');
	include('../includes/config.php');
	require_once __DIR__ . '/../includes/csrf.php';
	require_once __DIR__ . '/../includes/flash.php';

	// CSRF check
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if (!isset($_POST['csrf_token']) || !csrf_verify($_POST['csrf_token'])) {
				flash_set('Invalid form submission.', 'danger');
				header('Location: create.php');
				exit;
			}
	}

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

	$nameEsc = $name;
	$descEsc = $description;

	$ins = mysqli_prepare($conn, "INSERT INTO categories (name, description) VALUES (?, ?)");
	if ($ins) {
		mysqli_stmt_bind_param($ins, 'ss', $nameEsc, $descEsc);
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
