<?php
session_start();
include('../../includes/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../index.php');
    exit();
}

$item_id     = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
$orderId     = isset($_POST['orderinfo_id']) ? intval($_POST['orderinfo_id']) : 0;
$nextIndex   = isset($_POST['next_index']) ? intval($_POST['next_index']) : 0;
$user_id     = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
$user_name   = isset($_POST['user_name']) ? trim($_POST['user_name']) : null;
$rating      = isset($_POST['rating']) && $_POST['rating'] !== '' ? intval($_POST['rating']) : null;
$comment     = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($item_id <= 0 || $comment === '') {
    header("Location: ../../product/show.php?id={$item_id}");
    exit();
}

// basic foul-word filter
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

// insert review
$ins = mysqli_prepare($conn, "INSERT INTO reviews (item_id, user_id, user_name, rating, comment) VALUES (?, ?, ?, ?, ?)");
if ($ins) {
    mysqli_stmt_bind_param($ins, 'iisss', $item_id, $user_id, $user_name, $rating, $comment_esc);
    $res = mysqli_stmt_execute($ins);
    mysqli_stmt_close($ins);
} else {
    $res = false;
}

// ✅ Redirect to next product in the order
if ($orderId > 0) {
    header("Location: review.php?order={$orderId}&index={$nextIndex}");
} else {
    // fallback: go back to product page
    header("Location: ../../product/show.php?id={$item_id}");
}
exit();
