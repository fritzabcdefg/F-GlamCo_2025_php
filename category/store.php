<?php
session_start();
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

	$nameEsc = mysqli_real_escape_string($conn, $name);
	$descEsc = mysqli_real_escape_string($conn, $description);

	$sql = "INSERT INTO categories (name, description) VALUES ('{$nameEsc}', '{$descEsc}')";
	$res = mysqli_query($conn, $sql);
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
