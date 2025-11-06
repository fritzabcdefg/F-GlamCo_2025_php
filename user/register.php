<?php
session_start();
include("../includes/header.php");
?>

<div class="container-fluid container-lg mt-4">
    <?php include("../includes/alert.php"); ?>
    <form action="store.php" method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="mb-3">
            <label for="confirmPass" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="confirmPass" name="confirmPass" required>
        </div>

        <button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
