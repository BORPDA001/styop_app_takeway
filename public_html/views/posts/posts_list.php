<?php
require '../server/db.php';
require_once '../server/posts/posts.php';
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/helper.php';

$user_id = $_SESSION['user']['id'] ?? 0;

$result = mysqli_query($con, "SELECT * FROM posts ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="hy">
<head>
    <meta charset="UTF-8" />
    <title>Գրառումների ցանկ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        td.content-cell {
            max-width: 300px;
            white-space: normal !important;
            word-break: break-word;
        }
        .like-btn.liked {
            color: red !important;
            font-weight: bold;
            border-color: red;
        }
        video {
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
        }
        .btn-outline-dark:hover {
            background-color: #343a40;
            color: white;
        }
        .btn-outline-primary:hover {
            background-color: #0d6efd;
            color: white;
        }
        .btn-outline-danger:hover {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4 text-center fw-bold">📋 Գրառումների ցանկ</h1>

    <div class="table-responsive shadow-sm rounded">
        <table class="table table-bordered table-hover align-middle bg-white">
            <thead class="table-dark text-center">
            <tr>
                <th>ID</th>
                <th>Վերնագիր</th>
                <th>Բովանդակություն</th>
                <th>Վիդեո</th>
                <th>Ստեղծման ժամանակ</th>
                <th>Թարմացման ժամանակ</th>
                <th>Գործողություններ</th>
                <th style="width: 110px;">Լայք</th>
            </tr>
            </thead>
            <tbody>
            <?php while($post = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td class="text-center"><?= $post['id'] ?></td>
                    <td><?= htmlspecialchars($post['title']) ?></td>
                    <td class="content-cell"><?= htmlspecialchars($post['content']) ?></td>

                    <td style="min-width: 300px;">
                        <?php if (!empty($post['video_path'])): ?>
                            <div class="ratio ratio-4x3 rounded overflow-hidden">
                                <video controls autoplay muted>
                                    <source src="<?= base_url(htmlspecialchars($post['video_path'])) ?>" type="video/mp4">
                                    Ձեր դիտարկիչը չի աջակցում վիդեոներ։
                                </video>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>

                    <td class="text-nowrap"><?= $post['created_at'] ?></td>
                    <td class="text-nowrap"><?= $post['updated_at'] ?></td>

                    <td class="text-center">
                        <div class="btn-group d-flex flex-column gap-1">
                            <a href="web.php?action=post_view&id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-dark">Դիտել</a>

                            <?php if ($user_id === $post['user_id']): ?>
                                <a href="web.php?action=post_edit_form&id=<?= $post['id'] ?>" class="btn btn-sm btn-outline-primary">Փոփոխել</a>
                                <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger delete-btn"
                                        data-id="<?= $post['id'] ?>"
                                        data-title="<?= htmlspecialchars($post['title']) ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteModal"
                                >
                                    Ջնջել
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>

                    <td class="text-center">
                        <?php if ($user_id > 0): ?>
                            <button
                                    class="btn btn-sm btn-outline-primary like-btn <?= hasUserLikedPost($con, $user_id, $post['id']) ? 'liked' : '' ?>"
                                    data-post-id="<?= $post['id'] ?>"
                            >
                                ❤️ <span class="like-count"><?= getPostLikesCount($con, $post['id']) ?></span>
                            </button>
                        <?php else: ?>
                            <span class="text-muted small">Մուտք գործեք</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4">
        <a href="web.php?action=index" class="btn btn-outline-secondary">⬅️ Վերադառնալ գլխավոր էջ</a>
        <?php if ($user_id > 0): ?>
            <a href="web.php?action=post_create_form" class="btn btn-success">➕ Ավելացնել նոր գրառում</a>
        <?php endif; ?>
    </div>
</div>
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="web.php?action=post_delete" method="post">
            <input type="hidden" name="id" id="delete-post-id">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Հաստատե՞լ ջնջումը</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Փակել"></button>
                </div>
                <div class="modal-body">
                    Վստա՞հ ես, որ ցանկանում ես ջնջել գրառումը՝ <strong id="post-title-preview"></strong>։
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Չեղարկել</button>
                    <button type="submit" class="btn btn-danger">Այո, Ջնջել</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(function () {
        $('.like-btn').click(function () {
            let btn = $(this);
            let postId = btn.data('post-id');

            $.post('../routes/web.php?action=post_like', { post_id: postId }, function (res) {
                let countElem = btn.find('.like-count');
                let currentCount = parseInt(countElem.text()) || 0;

                if (res.success === 'liked') {
                    countElem.text(currentCount + 1);
                    btn.addClass('liked');
                } else if (res.success === 'unliked') {
                    countElem.text(Math.max(currentCount - 1, 0));
                    btn.removeClass('liked');
                } else if (res.error) {
                    alert(res.error);
                } else {
                    alert('Դուք պետք է մուտք գործած լինեք լայք անելու համար։');
                }
            }, 'json');
    });
    $('.delete-btn').on('click', function(){
            const postId = $(this).data('id');
            const postTitle = $(this).data('title');
            $('#delete-post-id').val(postId);
            $('#post-title-preview').text(postTitle);
        });
    });
</script>
<?php require __DIR__ . '/../layouts/footer.php'; ?>