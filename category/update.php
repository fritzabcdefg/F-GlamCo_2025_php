<?php
session_start();
include('../includes/config.php');

if (isset($_POST['submit'])) {
	$id = intval($_POST['category_id']);
	$name = trim($_POST['name']);
	$description = trim($_POST['description']);

	if ($name === '') {
		$_SESSION['cat_name_error'] = 'Please enter a category name.';
		header("Location: edit.php?id={$id}");
		exit();
	}

	$nameEsc = mysqli_real_escape_string($conn, $name);
	$descEsc = mysqli_real_escape_string($conn, $description);

	$sql = "UPDATE categories SET name = '{$nameEsc}', description = '{$descEsc}' WHERE category_id = {$id}";
	$res = mysqli_query($conn, $sql);
	if ($res) {
		header('Location: index.php');
		exit();
	} else {
		echo 'Error: ' . mysqli_error($conn);
	}
}
