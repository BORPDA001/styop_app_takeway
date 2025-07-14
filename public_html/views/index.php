<?php
$page_title = "Title";
require '../server/db.php';
require '../views/layouts/header.php';
$result = mysqli_query($con, "SELECT * FROM posts ORDER BY created_at DESC");
$logged_in = isset($_SESSION['user']);
?>
    <main class="container py-5">
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold">
                Բարի գալուստ<?= $logged_in ? ', ' . htmlspecialchars($_SESSION['user']['name']) : '' ?>!
            </h1>
            <p class="lead text-muted">
                Դու <?= $logged_in ? 'մուտք ես գործել հաջողությամբ։ Այստեղ կարող ես ավելացնել քո Post-երը։' : 'կարող ես դիտել գրառումների ցանկը, բայց ստեղծել միայն մուտք գործածները կարող են։' ?>
            </p>
            <hr class="my-4" />
        </div>

        <?php if ($logged_in): ?>
            <div class="text-center mb-4">
                <a href="../routes/web.php?action=post_create_form" class="btn btn-success btn-lg rounded-pill px-5">
                    ➕ Ավելացնել նոր գրառում
                </a>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="text-center mb-4">Posts</h2>

                <?php if ($logged_in): ?>
                    <div class="text-end mb-3">
                        <a href="../routes/web.php?action=posts_list" class="btn btn-outline-primary btn-sm rounded-pill">
                            <i class="bi bi-journal-text me-1"></i> Գրառումների Ցուցակ
                        </a>
                    </div>
                <?php endif; ?>

                <div class="list-group">
                    <?php while ($post = mysqli_fetch_assoc($result)): ?>
                    <?php if ($logged_in): ?>
                    <a href="../routes/web.php?action=post_view&id=<?= $post['id'] ?>"
                       class="list-group-item list-group-item-action mt-3 rounded shadow-sm">
                        <?php else: ?>
                        <div class="list-group-item mt-3 rounded shadow-sm disabled-link">
                            <?php endif; ?>

                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1"><?= htmlspecialchars($post['title']) ?></h5>
                                <small><?= $post['created_at'] ?></small>
                            </div>
                            <p class="mb-1 text-truncate"><?= htmlspecialchars($post['content']) ?></p>

                            <?php if ($logged_in): ?>
                    </a>
                    <?php else: ?>
                </div>
            <?php endif; ?>
            <?php endwhile; ?>
            </div>
        </div>
    </main>
<?php require 'layouts/footer.php'; ?>