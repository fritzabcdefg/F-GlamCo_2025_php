<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/header.php');
include('../includes/config.php');

// Fetch categories for dropdown
$categories = [];
$catRes = mysqli_query($conn, "SELECT category_id, name FROM categories ORDER BY name ASC");
if ($catRes) {
    while ($c = mysqli_fetch_assoc($catRes)) {
        $categories[] = $c;
    }
}
?>

<link rel="stylesheet" href="../style/style.css">

<div class="container form-wrapper">
    <form method="POST" action="store.php" enctype="multipart/form-data" class="product-form">
        <div class="form-group">
            <!-- Item Name -->
            <label for="name">Item Name</label>
            <input type="text" id="name" name="name" class="form-control"
                   placeholder="Enter item name"
                   value="<?php echo isset($_SESSION['name']) ? $_SESSION['name'] : ''; ?>" />
            <small class="error">
                <?php
                if (isset($_SESSION['nameError'])) {
                    echo $_SESSION['nameError'];
                    unset($_SESSION['nameError']);
                }
                ?>
            </small>

            <!-- Category Dropdown -->
            <label for="category">Category</label>
            <select name="category_id" id="category" class="form-control" required>
                <option value="">-- Select category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['category_id']; ?>"
                        <?php if (isset($_SESSION['category_id']) && $_SESSION['category_id'] == $cat['category_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

           <!-- Cost Price -->
<label for="cost">Cost Price</label>
<input type="text" id="cost" name="cost_price" class="form-control"
       placeholder="Enter item cost price"
       value="<?php echo isset($_SESSION['cost']) ? $_SESSION['cost'] : ''; ?>" />

<!-- Selling Price -->
<label for="sell">Selling Price</label>
<input type="text" id="sell" name="sell_price" class="form-control"
       placeholder="Enter selling price"
       value="<?php echo isset($_SESSION['sell']) ? $_SESSION['sell'] : ''; ?>" />

<!-- Supplier Name -->
<label for="supplier">Supplier Name</label>
<input type="text" id="supplier" name="supplier_name" class="form-control"
       placeholder="Enter supplier name"
       value="<?php echo isset($_SESSION['supplier_name']) ? $_SESSION['supplier_name'] : ''; ?>" />

<!-- Quantity -->
<label for="qty">Quantity</label>
<input type="number" id="qty" name="quantity" class="form-control"
       placeholder="Enter quantity"
       value="<?php echo isset($_SESSION['qty']) ? $_SESSION['qty'] : ''; ?>" />


            <!-- Item Images -->
            <label for="img_paths">Item Images</label>
            <input class="form-control" type="file" name="img_paths[]" multiple accept="image/*" /><br />
    
        </div>

        <!-- Buttons -->
        <div class="form-buttons">
            <button type="submit" name="submit" value="submit" class="btn btn-submit">Submit</button>
            <a href="index.php" class="btn btn-cancel">Cancel</a>
        </div>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
