<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../user/login.php?error=unauthorized");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?error=adminonly");
    exit();
}

if (!isset($_POST['submit'])) {
    header("Location: index.php");
    exit();
}

$item_id     = intval($_POST['item_id']);
$name        = trim($_POST['name']);
$description = trim($_POST['description'] ?? '');
$category_id = ($_POST['category_id'] !== '') ? intval($_POST['category_id']) : null;
$quantity    = intval($_POST['quantity']);
$supplier    = trim($_POST['supplier_name'] ?? '');

$cost_price  = isset($_POST['cost_price']) ? round((float)$_POST['cost_price'], 2) : 0.0;
$sell_price  = isset($_POST['sell_price']) ? round((float)$_POST['sell_price'], 2) : 0.0;

if ($item_id <= 0 || $name === '') {
    header("Location: edit.php?id={$item_id}");
    exit();
}

try {
    $conn->begin_transaction();

    if ($category_id === null) {
        $upd = mysqli_prepare($conn,
            "UPDATE items 
             SET name=?, description=?, cost_price=?, sell_price=?, supplier_name=?, category_id=NULL 
             WHERE item_id=?"
        );
        if (!$upd) throw new Exception("Prepare failed: " . mysqli_error($conn));
        mysqli_stmt_bind_param($upd, 'ssdssi', $name, $description, $cost_price, $sell_price, $supplier, $item_id);
    } else {
        $upd = mysqli_prepare($conn,
            "UPDATE items 
             SET name=?, description=?, cost_price=?, sell_price=?, supplier_name=?, category_id=? 
             WHERE item_id=?"
        );
        if (!$upd) throw new Exception("Prepare failed: " . mysqli_error($conn));
        mysqli_stmt_bind_param($upd, 'ssdssii', $name, $description, $cost_price, $sell_price, $supplier, $category_id, $item_id);
    }

    if (!mysqli_stmt_execute($upd)) {
        throw new Exception("Update failed: " . mysqli_error($conn));
    }
    mysqli_stmt_close($upd);

    $q = mysqli_prepare($conn, "SELECT 1 FROM stocks WHERE item_id=? LIMIT 1");
    mysqli_stmt_bind_param($q, 'i', $item_id);
    mysqli_stmt_execute($q);
    $resQ = mysqli_stmt_get_result($q);
    $exists = ($resQ && mysqli_num_rows($resQ) > 0);
    mysqli_stmt_close($q);

    if ($exists) {
        $u = mysqli_prepare($conn, "UPDATE stocks SET quantity=? WHERE item_id=?");
        mysqli_stmt_bind_param($u, 'ii', $quantity, $item_id);
        if (!mysqli_stmt_execute($u)) {
            throw new Exception("Stock update failed: " . mysqli_error($conn));
        }
        mysqli_stmt_close($u);
    } else {
        $insS = mysqli_prepare($conn, "INSERT INTO stocks (item_id, quantity) VALUES (?, ?)");
        mysqli_stmt_bind_param($insS, 'ii', $item_id, $quantity);
        if (!mysqli_stmt_execute($insS)) {
            throw new Exception("Stock insert failed: " . mysqli_error($conn));
        }
        mysqli_stmt_close($insS);
    }

    $conn->commit();
    header('Location: index.php?msg=Item+updated+successfully');
    exit();
} catch (Exception $e) {
    $conn->rollback();
    die("Transaction failed: " . htmlspecialchars($e->getMessage()));
}
?>
