<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/config.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id) {
	$sql = "DELETE FROM categories WHERE category_id = {$id}";
	$res = mysqli_query($conn, $sql);
}
header('Location: index.php');
exit();
