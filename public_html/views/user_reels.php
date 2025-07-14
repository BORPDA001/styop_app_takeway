<?php
include __DIR__.'/layouts/header.php';
require '../server/db.php';
require_once '../server/posts/posts.php';

$viewer_id = $_SESSION['user']['id'] ?? 0;
$author_id = isset($_GET['id']) ? intval($_GET['id']) : $viewer_id;
$isFollowing = isFollowing($con, $viewer_id, $author_id);
$followersCount = getFollowersCount($con, $author_id);
$followingCount = getFollowingCount($con, $author_id);
$videos = [];
if ($author_id > 0) {
    $stmt = $con->prepare("SELECT posts.*, users.name AS author_name FROM posts JOIN users ON posts.user_id = users.id WHERE posts.user_id = ? AND posts.video_path != '' ORDER BY posts.created_at DESC");
    $stmt->bind_param("i", $author_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $row['full_url'] = '/public_html/' . $row['video_path'];
        $videos[] = $row;
    }
}
?>
<div class="container py-4">
    <div class="text-center mb-4">
        <h3><?= $viewer_id == $author_id ? 'My Profile' : htmlspecialchars($videos[0]['author_name'] ?? 'Օգտատեր') . ' - Profile' ?></h3>
        <p class="text-center text-muted">
            <strong class="followers-count"><?= $followersCount ?></strong> Followers |
            <strong><?= $followingCount ?></strong> Following
        </p>
        <?php if ($viewer_id != $author_id): ?>
            <div class="text-center mb-3">
                <button id="follow-btn"
                        class="btn btn-sm <?= $isFollowing ? 'btn-outline-secondary' : 'btn-outline-primary' ?>"
                        data-id="<?= $author_id ?>">
                    <?= $isFollowing ? 'Unfollow' : 'Follow' ?>
                </button>
            </div>
        <?php endif; ?>


    </div>

    <?php if (count($videos) === 0): ?>
        <p class="text-center text-muted">Այս օգտատերը դեռևս video-ով գրառումներ չունի։</p>
    <?php else: ?>
        <div class="row g-4 justify-content-center">
            <?php foreach ($videos as $video): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card bg-dark text-white shadow-sm border-0">
                        <video class="card-img-top" controls style="max-height:300px;">
                            <source src="<?= htmlspecialchars($video['full_url']) ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($video['title']) ?></h5>
                            <p class="card-text small text-light-emphasis">
                                <?= htmlspecialchars(mb_strlen($video['content']) > 100 ? mb_substr($video['content'], 0, 100) . '...' : $video['content']) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const followBtn = document.getElementById('follow-btn');
        if (!followBtn) return;

        followBtn.addEventListener('click', () => {
            const userId = followBtn.getAttribute('data-id');

            fetch('web.php?action=toggle_follow', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `followed_id=${userId}`
            })
                .then(res => res.text())
                .then(text => {
                    try {
                        const data = JSON.parse(text);

                        if (data.status === 'followed') {
                            followBtn.textContent = 'Unfollow';
                            followBtn.classList.remove('btn-outline-primary');
                            followBtn.classList.add('btn-outline-secondary');
                        } else if (data.status === 'unfollowed') {
                            followBtn.textContent = 'Follow';
                            followBtn.classList.remove('btn-outline-secondary');
                            followBtn.classList.add('btn-outline-primary');
                        }

                        // live update follower count
                        const followersCount = document.querySelector('.followers-count');
                        if (followersCount && data.followersCount !== undefined) {
                            followersCount.textContent = data.followersCount;
                        }
                    } catch (err) {
                        console.error('JSON parse error:', text);
                    }
                });
        });
    });
</script>
<?php include __DIR__ . '/layouts/footer.php'; ?>
