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

$review_id = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;
$item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
$user_name = isset($_POST['user_name']) ? trim($_POST['user_name']) : null;
$rating = isset($_POST['rating']) && $_POST['rating'] !== '' ? intval($_POST['rating']) : null;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($review_id <= 0 || $item_id <= 0 || $comment === '') {
    flash_set('Please provide a comment.', 'danger');
    header("Location: ../../product/show.php?id={$item_id}");
    exit();
}

// check ownership
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
if (!$user_id) {
    flash_set('Please log in to edit reviews.', 'warning');
    header("Location: ../../product/show.php?id={$item_id}");
    exit();
}

$check_sql = "SELECT user_id FROM reviews WHERE id = {$review_id} LIMIT 1";
$check_res = mysqli_query($conn, $check_sql);
if (!$check_res || mysqli_num_rows($check_res) == 0) {
    flash_set('Review not found.', 'danger');
    header("Location: ../../product/show.php?id={$item_id}");
    exit();
}
$review = mysqli_fetch_assoc($check_res);
if ($review['user_id'] != $user_id) {
    flash_set('You can only edit your own reviews.', 'danger');
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

// update review
$upd = mysqli_prepare($conn, "UPDATE reviews SET user_name = ?, rating = ?, comment = ? WHERE id = ?");
if ($upd) {
    mysqli_stmt_bind_param($upd, 'sisi', $user_name, $rating, $comment_esc, $review_id);
    $res = mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);
} else {
    $res = false;
}

if ($res) {
    flash_set('Review updated.', 'success');
} else {
    flash_set('Failed to update review.', 'danger');
}
header("Location: ../../product/show.php?id={$item_id}");
exit();
?>
