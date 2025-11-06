<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$category = null;
if ($id) {
	$sql = "SELECT * FROM categories WHERE category_id = {$id} LIMIT 1";
	$res = mysqli_query($conn, $sql);
	if ($res && mysqli_num_rows($res) > 0) {
		$category = mysqli_fetch_assoc($res);
	}
}
?>

<div class="container mt-4">
	<h3><?php echo $category ? 'Edit Category' : 'Create Category'; ?></h3>
	<form action="<?php echo $category ? 'update.php' : 'store.php'; ?>" method="POST">
		<?php if ($category): ?>
			<input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
		<?php endif; ?>
		<div class="mb-3">
			<label for="name" class="form-label">Name</label>
			<input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($category['name'] ?? ''); ?>">
		</div>
		<div class="mb-3">
			<label for="description" class="form-label">Description</label>
			<textarea name="description" id="description" class="form-control"><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
		</div>
		<button type="submit" name="submit" class="btn btn-primary"><?php echo $category ? 'Save' : 'Create'; ?></button>
		<a href="index.php" class="btn btn-secondary">Cancel</a>
	</form>
</div>

<?php include('../includes/footer.php'); ?>
