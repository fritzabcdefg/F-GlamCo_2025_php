<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/header.php');
include('../includes/config.php');
require_once __DIR__ . '/../includes/csrf.php';

// var_dump($_SESSION);
// fetch categories for dropdown
$categories = [];
$catRes = mysqli_query($conn, "SELECT category_id, name FROM categories ORDER BY name ASC");
if ($catRes) {
    while ($c = mysqli_fetch_assoc($catRes)) {
        $categories[] = $c;
    }
}
?>

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

                <small>
                    <?php
                    if (isset($_SESSION['nameError'])) {
                        echo $_SESSION['nameError'];
                        unset($_SESSION['nameError']);
                    }
                    ?>
                </small>
                <label for="category">Category</label>
                <select name="category_id" id="category" class="form-control mb-3">
                    <option value="">-- Select category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>" <?php if(isset($_SESSION['category_id']) && $_SESSION['category_id'] == $cat['category_id']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="cost">Cost Price</label>
                <input
                    type="text"
                    class="form-control"
                    id="cost"
                    placeholder="Enter item cost price"
                    name="cost_price"
                    value="<?php
                            if (isset($_SESSION['cost']))
                                echo $_SESSION['cost'];
                            ?>" />
                <small>
                    <?php
                    if (isset($_SESSION['costError'])) {
                        echo $_SESSION['costError'];
                        unset($_SESSION['costError']);
                    }
                    ?></small>
                <label for="sell">Selling Price</label>

                <input type="text" class="form-control" id="sell" placeholder="Enter sell price" name="sell_price">
                <small>
                    <?php
                    if (isset($_SESSION['sellError'])) {
                        echo $_SESSION['sellError'];
                        unset($_SESSION['sellError']);
                    }
                    ?></small>

                <label for="qty">Quantity</label>

                <input type="number" class="form-control" id="qty" placeholder="1" name="quantity" />

                <label for="qty">Item Images</label>
                <input class="form-control" type="file" name="img_paths[]" multiple accept="image/*" /><br />
                <small class="form-text text-muted">You can select multiple images. The first image will be used as the main thumbnail.</small>
                <small>
                    <?php
                    if (isset($_SESSION['imageError'])) {
                        echo $_SESSION['imageError'];
                        unset($_SESSION['imageError']);
                    }
                    ?></small>

            </div>
            <button type="submit" class="btn btn-primary" name="submit" value="submit">Submit</button>
            <a href="index.php" role="button" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    include('../includes/footer.php');
    ?>