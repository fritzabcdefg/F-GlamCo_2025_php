<?php
session_start();
include('../includes/auth_admin.php');
include('../includes/config.php');

$_SESSION['name'] = trim($_POST['name']);
$_SESSION['cost'] = trim($_POST['cost_price']);
$_SESSION['sell'] = trim($_POST['sell_price']);
$_SESSION['qty'] = $_POST['quantity'];
$_SESSION['category_id'] = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;

if (isset($_POST['submit'])) {
    $cost = trim($_POST['cost_price']);
    $sell = trim($_POST['sell_price']);
    $name = trim($_POST['name']);
    $qty  = $_POST['quantity'];
    $target = '';

    if (empty($name)) {
        $_SESSION['nameError'] = 'Please input a Product name';
        header("Location: create.php");
        exit;
    }

    if (empty($cost) || !is_numeric($cost)) {
        $_SESSION['costError'] = 'error product price format';
        header("Location: create.php");
        exit;
    }

    if (empty($sell) || !is_numeric($sell)) {
        $_SESSION['sellError'] = 'error product price format';
        header("Location: create.php");
        exit;
    }

    if (empty($_POST['category_id'])) {
        $_SESSION['categoryError'] = 'Please select a category';
        header("Location: create.php");
        exit;
    }

    if (isset($_FILES['img_path']) && $_FILES['img_path']['error'] == 0) {
        if (in_array($_FILES['img_path']['type'], ["image/jpeg","image/jpg","image/png"])) {
            $source = $_FILES['img_path']['tmp_name'];
            $target = 'images/' . basename($_FILES['img_path']['name']);
            move_uploaded_file($source, $target) or die("Couldn't copy");
        } else {
            $_SESSION['imageError'] = "wrong file type";
            header("Location: create.php");
            exit;
        }
    }

    $category_id = intval($_POST['category_id']);

    $sql = "INSERT INTO items(name, category_id, cost_price, sell_price, img_path) 
            VALUES('{$name}', {$category_id}, '{$cost}', '{$sell}', '{$target}')";
    $result = mysqli_query($conn, $sql);

    $q_stock = "INSERT INTO stocks(item_id, quantity) VALUES(LAST_INSERT_ID(), {$qty})";
    $result2 = mysqli_query($conn, $q_stock);

    if ($result && $result2) {
        header("Location: index.php");
        exit;
    }
}
