<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/header.php');
include('../includes/config.php');
require_once __DIR__ . '/../includes/csrf.php';

// Fetch categories for dropdown
$categories = [];
$catRes = mysqli_query($conn, "SELECT category_id, name FROM categories ORDER BY name ASC");
if ($catRes) {
    while ($c = mysqli_fetch_assoc($catRes)) {
        $categories[] = $c;
    }
}
?>

<<<<<<< HEAD
<body>
    <div class="container">
        <form method="POST" action="store.php" enctype="multipart/form-data">
            <?php echo csrf_input(); ?>
            <div class="form-group">
                <label for="name">Item Name</label>
                <input type="text"
                    class="form-control"
                    id="name"
                    placeholder="Enter item name"
                    name="name"
                    value="<?php
                            if (isset($_SESSION['name']))
                                echo $_SESSION['name'];
                            ?>" />
=======
<link rel="stylesheet" href="../style/style.css">
>>>>>>> 61e5e3cab6850afcf1bd758b843d4db0a0ab3cb8

<body>
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
                <small class="error">
                    <?php
                    if (isset($_SESSION['costError'])) {
                        echo $_SESSION['costError'];
                        unset($_SESSION['costError']);
                    }
                    ?>
                </small>

                <!-- Selling Price -->
                <label for="sell">Selling Price</label>
                <input type="text" id="sell" name="sell_price" class="form-control"
                    placeholder="Enter selling price"
                    value="<?php echo isset($_SESSION['sell']) ? $_SESSION['sell'] : ''; ?>" />
                <small class="error">
                    <?php
                    if (isset($_SESSION['sellError'])) {
                        echo $_SESSION['sellError'];
                        unset($_SESSION['sellError']);
                    }
                    ?>
                </small>

                <!-- Quantity -->
                <label for="qty">Quantity</label>
                <input type="number" id="qty" name="quantity" class="form-control"
                    placeholder="Enter quantity"
                    value="<?php echo isset($_SESSION['qty']) ? $_SESSION['qty'] : ''; ?>" />

<<<<<<< HEAD
                <input type="number" class="form-control" id="qty" placeholder="1" name="quantity" />

                <label for="qty">Item Images</label>
                <input class="form-control" type="file" name="img_paths[]" multiple accept="image/*" /><br />
                <small class="form-text text-muted">You can select multiple images. The first image will be used as the main thumbnail.</small>
                <small>
=======
                <!-- Image Upload -->
                <label for="img_path">Item Image</label>
                <input type="file" name="img_path" class="form-control" />
                <small class="error">
>>>>>>> 61e5e3cab6850afcf1bd758b843d4db0a0ab3cb8
                    <?php
                    if (isset($_SESSION['imageError'])) {
                        echo $_SESSION['imageError'];
                        unset($_SESSION['imageError']);
                    }
                    ?>
                </small>
            </div>

            <!-- Buttons -->
            <div class="form-buttons">
                <button type="submit" name="submit" value="submit" class="btn btn-submit">Submit</button>
                <a href="index.php" class="btn btn-cancel">Cancel</a>
            </div>
        </form>
    </div>

<?php include('../includes/footer.php'); ?>
