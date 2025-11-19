<?php
session_start();
include('../../includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../index.php');
    exit();
}

$review_id    = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;
$item_id      = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
$orderinfo_id = isset($_POST['orderinfo_id']) ? intval($_POST['orderinfo_id']) : 0;
$user_name    = isset($_POST['user_name']) ? trim($_POST['user_name']) : null;
$rating       = isset($_POST['rating']) && $_POST['rating'] !== '' ? intval($_POST['rating']) : null;
$comment      = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($review_id <= 0 || $item_id <= 0 || $comment === '') {
    header("Location: ../../product/show.php?id={$item_id}");
    exit();
}

// check ownership
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
if (!$user_id) {
    header("Location: ../../product/show.php?id={$item_id}");
    exit();
}

$check_sql = "SELECT user_id FROM reviews WHERE id = {$review_id} LIMIT 1";
$check_res = mysqli_query($conn, $check_sql);
if (!$check_res || mysqli_num_rows($check_res) == 0) {
    header("Location: ../../product/show.php?id={$item_id}");
    exit();
}
$review = mysqli_fetch_assoc($check_res);
if ($review['user_id'] != $user_id) {
    header("Location: ../../product/show.php?id={$item_id}");
    exit();
}

// foul-word filter
$banned = ['fuck','shit','bitch','damn','asshole','crap','dick','piss','puta','bobo','tanga'];
$commentFiltered = $comment;
foreach ($banned as $bad) {
    $pattern = '/\b(' . preg_quote($bad, '/') . ')\b/i';
    $commentFiltered = preg_replace_callback($pattern, function($m) {
        $w = $m[1];
        $len = mb_strlen($w);
        if ($len <= 2) return str_repeat('*', $len);
        $first = mb_substr($w, 0, 1);
        $last  = mb_substr($w, -1, 1);
        return $first . str_repeat('*', max(1, $len - 2)) . $last;
    }, $commentFiltered);
}

$comment_esc = $commentFiltered;

// update review including orderinfo_id
$upd = mysqli_prepare($conn, "UPDATE reviews SET user_name = ?, rating = ?, comment = ?, orderinfo_id = ? WHERE id = ?");
if ($upd) {
    mysqli_stmt_bind_param($upd, 'sisii', $user_name, $rating, $comment_esc, $orderinfo_id, $review_id);
    $res = mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);
} else {
    $res = false;
}

header("Location: ../../product/show.php?id={$item_id}");
exit();
