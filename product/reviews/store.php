<?php
session_start();
include('../../includes/config.php');
require_once __DIR__ . '/../../includes/csrf.php';

require_once __DIR__ . '/../../includes/flash.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../index.php');
    exit();
}

// CSRF
if (!isset($_POST['csrf_token']) || !csrf_verify($_POST['csrf_token'])) {
    flash_set('Invalid form submission.', 'danger');
    header('Location: ../../index.php');
    exit();
}

$item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
$user_name = isset($_POST['user_name']) ? trim($_POST['user_name']) : null;
$rating = isset($_POST['rating']) && $_POST['rating'] !== '' ? intval($_POST['rating']) : null;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($item_id <= 0 || $comment === '') {
    flash_set('Please provide a comment.', 'danger');
    header("Location: ../../product/show.php?id={$item_id}");
    exit();
}

// basic foul-word filter: mask banned words by replacing middle chars with *
$banned = ['fuck','shit','bitch','damn','asshole','crap','dick','piss'];
$commentFiltered = $comment;
foreach ($banned as $bad) {
    $pattern = '/\b(' . preg_quote($bad, '/') . ')\b/i';
    $commentFiltered = preg_replace_callback($pattern, function($m) {
        $w = $m[1];
        $len = mb_strlen($w);
        if ($len <= 2) return str_repeat('*', $len);
        $first = mb_substr($w,0,1);
        $last = mb_substr($w,-1,1);
        return $first . str_repeat('*', max(1,$len-2)) . $last;
    }, $commentFiltered);
}

$comment_esc = $commentFiltered;

// prepare insert; handle optional user_name
if ($user_name) {
    $ins = mysqli_prepare($conn, "INSERT INTO reviews (item_id, user_name, rating, comment) VALUES (?, ?, ?, ?)");
    if ($ins) {
        mysqli_stmt_bind_param($ins, 'isis', $item_id, $user_name, $rating, $comment_esc);
        $res = mysqli_stmt_execute($ins);
        mysqli_stmt_close($ins);
    } else {
        $res = false;
    }
} else {
    $ins = mysqli_prepare($conn, "INSERT INTO reviews (item_id, user_name, rating, comment) VALUES (?, NULL, ?, ?)");
    if ($ins) {
        mysqli_stmt_bind_param($ins, 'iss', $item_id, $rating, $comment_esc);
        $res = mysqli_stmt_execute($ins);
        mysqli_stmt_close($ins);
    } else {
        $res = false;
    }
}

if ($res) {
    flash_set('Review submitted.', 'success');
}
header("Location: ../../product/show.php?id={$item_id}");
exit();
?>
