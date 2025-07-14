<?php
require '../server/db.php';
require '../server/posts/posts.php';

header('Content-Type: application/json');

$post_id = intval($_GET['post_id'] ?? 0);

if ($post_id > 0) {
    $comments = getPostComments($con, $post_id);
    echo json_encode($comments);
} else {
    echo json_encode([]);
}
exit;
