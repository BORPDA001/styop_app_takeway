<?php
include __DIR__.'/layouts/header.php';
require '../server/db.php';
require_once '../server/posts/posts.php';
$viewer_id = $_SESSION['user']['id'] ?? 0;
$user_id = $_SESSION['user']['id'] ?? 0;
$result = mysqli_query($con, "
    SELECT posts.*, users.name AS author_name, users.id AS author_id
    FROM posts
    JOIN users ON users.id = posts.user_id
    WHERE video_path != ''
    ORDER BY RAND()
");
$videos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['full_url'] = '/public_html/' . $row['video_path'];
    $videos[] = $row;
}
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Reels</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <style>
            body {
                background-color: #000;
                color: #fff;
                overflow-x: hidden;
            }
            .reels-container {
                height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                position: relative;
            }
            .video-wrapper {
                position: relative;
                width: 100%;
                max-width: 600px;
                height: 70vh;
                overflow: hidden;
                border-radius: 12px;
                box-shadow: 0 0 20px rgba(255,255,255,0.1);
            }
            .video-item {
                position: absolute;
                width: 100%;
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                opacity: 0;
                transition: all 0.5s ease;
                transform: translateY(100%);
            }
            .video-item.active {
                opacity: 1;
                transform: translateY(0);
                z-index: 1;
            }
            .video-item.exit-up {
                transform: translateY(-100%);
                opacity: 0;
            }
            .video-item.exit-down {
                transform: translateY(100%);
                opacity: 0;
            }
            video {
                max-height: 100%;
                width: auto;
                max-width: 100%;
                background: #000;
            }
            .video-info {
                margin-top: 1rem;
                max-width: 90%;
                text-align: center;
                background: rgba(0, 0, 0, 0.6);
                backdrop-filter: blur(10px);
                padding: 1.2rem;
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                transition: all 0.3s ease;
                border: 1px solid rgba(255, 255, 255, 0.1);
            }

            .video-info h5 {
                font-weight: bold;
                color: #fff;
                margin-bottom: 0.5rem;
                font-size: 1.2rem;
                text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
            }

            .video-info p {
                color: #ddd;
                font-size: 0.95rem;
                line-height: 1.5;
                margin-bottom: 1rem;
            }

            .video-info .author-info {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                margin-bottom: 0.8rem;
            }

            .video-info .author-info i {
                font-size: 1.2rem;
                color: #fff;
            }

            .video-info .btn-group {
                display: flex;
                gap: 10px;
                justify-content: center;
                flex-wrap: wrap;
            }

            .video-info .btn-outline-light {
                transition: all 0.2s ease;
                border-radius: 20px;
                padding: 0.25rem 1rem;
            }

            .video-info .btn-outline-light:hover {
                background-color: rgba(255, 255, 255, 0.1);
            }

            .video-info .follow-btn {
                border-radius: 20px;
                padding: 0.25rem 1rem;
                font-weight: 500;
                transition: all 0.2s ease;
            }

            .video-info .follow-btn.btn-outline-primary:hover {
                background-color: rgba(13, 110, 253, 0.2);
            }

            .video-info .follow-btn.btn-outline-secondary:hover {
                background-color: rgba(108, 117, 125, 0.2);
            }
            .nav-btn {
                position: absolute;
                background: rgba(255, 255, 255, 0.1);
                border: none;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                color: white;
                font-size: 1.5rem;
                transition: all 0.2s ease;
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10;
            }
            .nav-btn:hover {
                background: rgba(255, 255, 255, 0.3);
                transform: scale(1.1);
            }
            #prev-btn {
                top: 20px;
            }
            #next-btn {
                bottom: 20px;
            }
            .back-btn {
                position: absolute;
                top: 20px;
                left: 20px;
                z-index: 100;
            }
            .progress-container {
                position: absolute;
                top: 10px;
                left: 0;
                right: 0;
                width: 100%;
                padding: 0 15px;
                z-index: 10;
            }
            .progress {
                height: 3px;
                background-color: rgba(255, 255, 255, 0.3);
            }
            .progress-bar {
                background-color: white;
                transition: width 0.1s linear;
            }
        </style>
    </head>
<body>

<div class="reels-container">
    <div class="progress-container">
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
        </div>
    </div>

    <div class="video-wrapper">
        <?php foreach ($videos as $index => $video): ?>
            <div class="video-item <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>">
                <video controls playsinline>
                    <source src="<?= htmlspecialchars($video['full_url']) ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <div class="video-info">
                    <h5><?= htmlspecialchars($video['title']) ?></h5>

                    <p>
                        <?= htmlspecialchars(mb_strlen($video['content']) > 150
                            ? mb_substr($video['content'], 0, 150) . '...'
                            : $video['content']) ?>
                    </p>

                    <div class="author-info">
                        <i class="bi bi-person-circle"></i>
                        <span><?= htmlspecialchars($video['author_name']) ?></span>
                    </div>

                    <div class="btn-group">
                        <a href="web.php?action=user_reels&id=<?= $video['author_id'] ?>"
                           class="btn btn-sm btn-outline-light">
                            <?= ($viewer_id === $video['author_id']) ? 'My Profile' : 'User Profile' ?>
                        </a>

                        <?php if ($viewer_id && $viewer_id !== $video['author_id']): ?>
                            <button class="btn btn-sm follow-btn <?= isFollowing($con, $viewer_id, $video['author_id']) ? 'btn-outline-secondary' : 'btn-outline-primary' ?>"
                                    data-id="<?= $video['author_id'] ?>">
                                <?= isFollowing($con, $viewer_id, $video['author_id']) ? 'Unfollow' : 'Follow' ?>
                            </button>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-outline-light comment-btn"
                                data-post-id="<?= $video['id'] ?>">
                            💬 Comments
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <button id="prev-btn" class="nav-btn" title="Previous">
        <i class="bi bi-arrow-up"></i>
    </button>

    <button id="next-btn" class="nav-btn" title="Next">
        <i class="bi bi-arrow-down"></i>
    </button>
</div>
<div class="modal fade" id="commentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Comments</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="comments-list" class="mb-3"></div>

                <form id="comment-form">
                    <input type="hidden" name="post_id" id="comment-post-id">
                    <div class="mb-2">
                        <textarea name="content" class="form-control" placeholder="Write a comment..." required></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-outline-light btn-sm">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const videoItems = document.querySelectorAll('.video-item');
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const progressBar = document.querySelector('.progress-bar');
        let currentIndex = 0;
        let progressInterval;
        let isPlaying = true;

        videoItems.forEach(item => {
            const video = item.querySelector('video');
            video.addEventListener('play', handleVideoPlay);
            video.addEventListener('pause', handleVideoPause);
            video.addEventListener('ended', handleVideoEnded);
        });

        playCurrentVideo();

        function playCurrentVideo() {
            videoItems.forEach(item => {
                const video = item.querySelector('video');
                video.pause();
                video.currentTime = 0;
                item.classList.remove('active', 'exit-up', 'exit-down');
            });

            const currentItem = videoItems[currentIndex];
            currentItem.classList.add('active');
            const currentVideo = currentItem.querySelector('video');

            currentVideo.muted = true;
            const playPromise = currentVideo.play();

            if (playPromise !== undefined) {
                playPromise.then(_ => {
                    startProgressBar(currentVideo);
                })
                    .catch(error => {
                        currentVideo.muted = false;
                        startProgressBar(currentVideo);
                    });
            }
        }

        function startProgressBar(video) {
            clearInterval(progressInterval);
            progressBar.style.width = '0%';

            progressInterval = setInterval(() => {
                if (video.duration) {
                    const progress = (video.currentTime / video.duration) * 100;
                    progressBar.style.width = `${progress}%`;
                }
            }, 100);
        }

        function handleVideoPlay(e) {
            isPlaying = true;
            startProgressBar(e.target);
        }

        function handleVideoPause(e) {
            isPlaying = false;
            clearInterval(progressInterval);
        }

        function handleVideoEnded(e) {
            nextVideo();
        }

        function nextVideo() {
            if (currentIndex < videoItems.length - 1) {
                transitionVideo(currentIndex, currentIndex + 1, 'down');
                currentIndex++;
            } else {
                transitionVideo(currentIndex, 0, 'down');
                currentIndex = 0;
            }
        }

        function prevVideo() {
            if (currentIndex > 0) {
                transitionVideo(currentIndex, currentIndex - 1, 'up');
                currentIndex--;
            } else {
                transitionVideo(currentIndex, videoItems.length - 1, 'up');
                currentIndex = videoItems.length - 1;
            }
        }

        function transitionVideo(fromIndex, toIndex, direction) {
            const fromItem = videoItems[fromIndex];
            const toItem = videoItems[toIndex];

            fromItem.classList.add(direction === 'up' ? 'exit-up' : 'exit-down');

            toItem.classList.add('active');

            setTimeout(() => {
                fromItem.classList.remove('active', 'exit-up', 'exit-down');
                playCurrentVideo();
            }, 500);
        }

        nextBtn.addEventListener('click', nextVideo);
        prevBtn.addEventListener('click', prevVideo);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowDown' || e.key === ' ') {
                nextVideo();
                e.preventDefault();
            } else if (e.key === 'ArrowUp') {
                prevVideo();
                e.preventDefault();
            }
        });

        let touchStartY = 0;
        let touchEndY = 0;

        document.addEventListener('touchstart', function(e) {
            touchStartY = e.changedTouches[0].screenY;
        }, false);

        document.addEventListener('touchend', function(e) {
            touchEndY = e.changedTouches[0].screenY;
            handleSwipe();
        }, false);

        function handleSwipe() {
            const threshold = 50;

            if (touchEndY < touchStartY - threshold) {
                nextVideo();
            } else if (touchEndY > touchStartY + threshold) {
                prevVideo();
            }
        }
    });
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.follow-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const userId = btn.getAttribute('data-id');

                fetch('web.php?action=toggle_follow', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'followed_id=' + encodeURIComponent(userId)
                })
                    .then(res => res.text())
                    .then(text => {
                        try {
                            const data = JSON.parse(text);

                            if (data.status === 'followed') {
                                btn.textContent = 'Unfollow';
                                btn.classList.remove('btn-outline-primary');
                                btn.classList.add('btn-outline-secondary');
                            } else if (data.status === 'unfollowed') {
                                btn.textContent = 'Follow';
                                btn.classList.remove('btn-outline-secondary');
                                btn.classList.add('btn-outline-primary');
                            }
                        } catch (err) {
                            console.error('JSON parse error:', text);
                        }
                    })
                    .catch(err => {
                        console.error('Request failed:', err);
                    });
            });
        });
    });
    $(document).ready(function () {
        let currentVideo = null;
        $('.comment-btn').on('click', function () {
            const postId = $(this).data('post-id');
            $('#comment-post-id').val(postId);
            currentVideo = $(this).closest('.video-item').find('video').get(0);
            if (currentVideo && !currentVideo.paused) {
                currentVideo.pause();
            }
            $.ajax({
                url: 'get_comments.php',
                method: 'GET',
                data: { post_id: postId },
                success: function (data) {
                    let html = '';
                    if (data.length == 0) {
                        html = '<p class="text-muted">No comments yet.</p>';
                    } else {
                        data.forEach(function (comment) {
                            html += `
                            <div class="mb-2 border-bottom pb-1">
                                <strong>${comment.name}</strong>
                                <p class="mb-0 small">${comment.content}</p>
                            </div>`;
                        });
                    }
                    $('#comments-list').html(html);
                    $('#commentModal').modal('show');
                }
            });
        });
        $('#comment-form').on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                url: 'web.php?action=add_comment',
                method: 'POST',
                data: $(this).serialize(),
                success: function (res) {
                    if (res.success) {
                        $('#comment-form textarea').val('');
                        $('.comment-btn[data-post-id="' + $('#comment-post-id').val() + '"]').click();
                    } else {
                        alert(res.error || 'Failed to add comment.');
                    }
                }
            });
        });
        $('#commentModal').on('hidden.bs.modal', function () {
            if (currentVideo) {
                currentVideo.play();
            }
        });
    });</script>
<?php include __DIR__ . '/layouts/footer.php'; ?>