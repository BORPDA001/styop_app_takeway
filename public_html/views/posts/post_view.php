<?php
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/helper.php';
require '../server/db.php';
require_once '../server/posts/posts.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo "Սխալ ID։";
    exit;
}

$post = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM posts WHERE id = $id"));
if (!$post) {
    echo "Գրառումը չի գտնվել։";
    exit;
}

$page_title = "Գրառման դիտում";
$user_id = $_SESSION['user']['id'] ?? 0;
$comments = getPostComments($con, $post['id']);
?>
<!doctype html>
<html lang="hy">
<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($post['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-5">
    <a href="web.php?action=posts_list" class="btn btn-secondary mb-4">&larr; Վերադառնալ</a>

    <div class="card shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <h2 class="card-title"><?= htmlspecialchars($post['title']) ?></h2>
            <p class="text-muted mb-2"><small>Ստեղծվել է՝ <?= $post['created_at'] ?></small></p>
            <hr>
            <p class="card-text"><?= nl2br(htmlspecialchars($post['content'])) ?></p>

            <?php if (!empty($post['video_path'])): ?>
                <div class="mt-4">
                    <video width="50%" class="rounded-3 border" controls>
                        <source src="<?= base_url(htmlspecialchars($post['video_path'])) ?>" type="video/mp4">
                        Ձեր դիտարկիչը չի աջակցում վիդեոներ:
                    </video>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="card shadow-sm rounded-4">
        <div class="card-body">
            <h5 class="mb-3">💬 Մեկնաբանություններ</h5>

            <?php if ($user_id): ?>
                <form id="comment-form" class="mb-3">
                    <div class="mb-2">
                        <textarea class="form-control" id="comment-content" rows="3" placeholder="Գրեք մեկնաբանություն..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Ուղարկել</button>
                </form>
            <?php else: ?>
                <div class="alert alert-warning mb-3">Մեկնաբանելու համար <a href="routes/web.php?action=login_form">մուտք գործեք</a>։</div>
            <?php endif; ?>

            <ul class="list-group" id="comment-list">
                <?php foreach ($comments as $comment): ?>
                    <li class="list-group-item">
                        <strong><?= htmlspecialchars($comment['name']) ?>:</strong>
                        <?= nl2br(htmlspecialchars($comment['content'])) ?>
                        <div class="text-muted small"><?= $comment['created_at'] ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $('#comment-form').on('submit', function (e) {
        e.preventDefault();
        let content = $('#comment-content').val().trim();
        if (!content) return;

        $.post('web.php?action=add_comment', {
            post_id: <?= $post['id'] ?>,
            content: content
        }, function (data) {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Սխալ։');
            }
        }, 'json');
    });
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
