<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/config.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id) {
	require_once __DIR__ . '/../includes/csrf.php';

	// require POST with valid CSRF token
	if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['csrf_token']) || !csrf_verify($_POST['csrf_token'])) {
		$_SESSION['message'] = 'Invalid request.';
		header('Location: index.php');
		exit;
	}

	$del = mysqli_prepare($conn, "DELETE FROM categories WHERE category_id = ?");
	if ($del) {
		mysqli_stmt_bind_param($del, 'i', $id);
		mysqli_stmt_execute($del);
		mysqli_stmt_close($del);
	}
}
header('Location: index.php');
exit();
