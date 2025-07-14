<?php
include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/helper.php';
require '../server/db.php';
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: web.php?action=posts_list');
    exit;
}

$result = mysqli_query($con, "SELECT * FROM posts WHERE id=$id LIMIT 1");
$post = mysqli_fetch_assoc($result);

if (!$post) {
    header('Location: web.php?action=posts_list');
    exit;
}

$errors = $_SESSION['post_errors'] ?? [];
$old = $_SESSION['post_old'] ?? [];
unset($_SESSION['post_errors'], $_SESSION['post_old']);
$title = "Post Edit";
$content = $old['content'] ?? $post['content'];
?>

<!DOCTYPE html>
<html lang="hy">
<head>
    <meta charset="UTF-8" />
    <title>Խմբագրել գրառում</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow-sm mx-auto" style="max-width: 700px;">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">✏️ Խմբագրել գրառում</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e): ?>
                        <div><?= htmlspecialchars($e) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="../routes/web.php?action=post_update" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $id ?>" />

                <div class="mb-3">
                    <label for="title" class="form-label">Վերնագիր</label>
                    <input type="text" class="form-control" id="title" name="title"
                           value="<?= htmlspecialchars($title) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Բովանդակություն</label>
                    <textarea class="form-control" id="content" name="content" rows="6"
                              required><?= htmlspecialchars($content) ?></textarea>
                </div>

                <?php if (!empty($post['video_path'])): ?>
                    <div class="mb-3">
                        <label class="form-label">Ներկայիս վիդեոն</label>
                        <div class="ratio ratio-16x9">
                            <video controls preload="metadata" class="w-100">
                                <source src="<?= base_url(htmlspecialchars($post['video_path'])) ?>" type="video/mp4">
                                Ձեր դիտարկիչը չի աջակցում վիդեոներ։
                            </video>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mb-4">
                    <label for="video" class="form-label">Փոխել վիդեոն (ըստ ցանկության)</label>
                    <input type="file" class="form-control" id="video" name="video" accept="video/mp4,video/webm">
                    <div class="form-text">Եթե նոր վիդեո չընտրես, կմնա հինը։</div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="web.php?action=posts_list" class="btn btn-secondary">⬅️ Հետ</a>
                    <button type="submit" class="btn btn-primary">💾 Պահպանել փոփոխությունները</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
