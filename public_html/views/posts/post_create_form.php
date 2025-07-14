<?php
require __DIR__ . '/../layouts/header.php';
$errors = $_SESSION['post_errors'] ?? [];
$old = $_SESSION['post_old'] ?? [];
unset($_SESSION['post_errors'], $_SESSION['post_old']);
?>
<!DOCTYPE html>
<html lang="hy">
<head>
    <meta charset="UTF-8" />
    <title>Նոր գրառում</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow-sm mx-auto" style="max-width: 700px;">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">➕ Ավելացնել նոր գրառում</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e): ?>
                        <div><?= htmlspecialchars($e) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="../routes/web.php?action=post_create" method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="post_create">

                <div class="mb-3">
                    <label for="title" class="form-label">Վերնագիր</label>
                    <input
                            type="text"
                            class="form-control"
                            id="title"
                            name="title"
                            value="<?= htmlspecialchars($old['title'] ?? '') ?>"
                            placeholder="Մուտքագրեք վերնագիրը"
                            required
                    >
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Բովանդակություն</label>
                    <textarea
                            class="form-control"
                            id="content"
                            name="content"
                            rows="6"
                            placeholder="Մուտքագրեք բովանդակությունը"
                            required
                    ><?= htmlspecialchars($old['content'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="video" class="form-label">Վիդեո (mp4 / webm)</label>
                    <input
                            class="form-control"
                            type="file"
                            id="video"
                            name="video"
                            accept="video/mp4,video/webm"
                    >
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-success">💾 Պահպանել</button>
                </div>
            </form>

            <div class="mt-3 text-center">
                <a href="web.php?action=posts_list" class="btn btn-link">⬅️ Վերադառնալ ցուցակին</a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>