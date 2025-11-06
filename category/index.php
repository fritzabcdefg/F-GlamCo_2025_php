<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

$sql = "SELECT * FROM categories";
$result = mysqli_query($conn, $sql);
$count = mysqli_num_rows($result);
?>

<body>
	<div class="container mt-4">
		<a href="create.php" class="btn btn-primary mb-3">Add Category</a>
		<h4>Categories (<?= $count ?>)</h4>
		<table class="table table-striped">
			<thead>
				<tr>
					<th>#</th>
					<th>Name</th>
					<th>Description</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php while ($row = mysqli_fetch_assoc($result)): ?>
					<tr>
						<td><?php echo $row['category_id']; ?></td>
						<td><?php echo htmlspecialchars($row['name']); ?></td>
						<td><?php echo htmlspecialchars($row['description']); ?></td>
						<td>
							<a href="edit.php?id=<?php echo $row['category_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
							<a href="delete.php?id=<?php echo $row['category_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this category?');">Delete</a>
						</td>
					</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
	</div>

<?php include('../includes/footer.php'); ?>
