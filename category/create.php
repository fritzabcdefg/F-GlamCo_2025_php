<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');
?>

<div class="container mt-4">
	<h3>Create Category</h3>
	<form action="store.php" method="POST">
		<div class="mb-3">
			<label for="name" class="form-label">Name</label>
			<input type="text" name="name" id="name" class="form-control" value="<?php echo isset($_SESSION['cat_name'])?htmlspecialchars($_SESSION['cat_name']):''; ?>">
			<small class="text-danger"><?php if(isset($_SESSION['cat_name_error'])) { echo $_SESSION['cat_name_error']; unset($_SESSION['cat_name_error']); } ?></small>
		</div>
		<div class="mb-3">
			<label for="description" class="form-label">Description</label>
			<textarea name="description" id="description" class="form-control"><?php echo isset($_SESSION['cat_description'])?htmlspecialchars($_SESSION['cat_description']):''; ?></textarea>
		</div>
		<button type="submit" name="submit" class="btn btn-primary">Create</button>
		<a href="index.php" class="btn btn-secondary">Cancel</a>
	</form>
</div>

<?php include('../includes/footer.php'); ?>
