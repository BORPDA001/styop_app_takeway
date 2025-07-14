<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

$action_raw = $_REQUEST['action'] ?? '';
$action = trim($action_raw, "\"'");
$action = trim($action);
require __DIR__ . '/../server/db.php';
require __DIR__ . '/../server/auth/auth.php';
require __DIR__ . '/../server/posts/posts.php';
require __DIR__ . '/helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($action === 'register') {
        $result = register($con, $_POST);
        if ($result['status']) {
            header('Location: web.php?action=login_form');
        } else {
            $_SESSION['register_errors'] = $result['errors'];
            $_SESSION['old'] = $_POST;
            header('Location: web.php?action=register_form');
        }
        exit;
    }

    else if ($action === 'login') {
        $result = login($con, $_POST);
        if ($result['status']) {
            $_SESSION['user'] = $result['user'];
            header('Location: web.php?action=index');
        } else {
            $_SESSION['login_errors'] = $result['errors'];
            $_SESSION['old'] = $_POST;
            header('Location: web.php?action=login_form');
        }
        exit;
    }

    else if ($action === 'post_create') {
        $result = createPost($con, $_POST['title'] ?? '', $_POST['content'] ?? '', $_FILES['video'] ?? []);
        if ($result['status']) {
            header('Location: web.php?action=posts_list');
        } else {
            $_SESSION['post_errors'] = $result['errors'];
            $_SESSION['post_old'] = $_POST;
            header('Location: web.php?action=post_create_form');
        }
        exit;
    }
    else if ($action === 'post_update') {
        $id = intval($_POST['id'] ?? 0);
        $video = $_FILES['video'] ?? null;

        $result = updatePost($con, $id, $_POST['title'] ?? '', $_POST['content'] ?? '', $video);

        if ($result['status']) {
            header('Location: web.php?action=posts_list');
        } else {
            $_SESSION['post_errors'] = $result['errors'];
            $_SESSION['post_old'] = $_POST;
            header('Location: web.php?action=post_edit_form&id=' . $id);
        }
        exit;
    }


    else if ($action === 'post_delete') {
        $id = intval($_POST['id'] ?? 0);
        deletePost($con, $id);
        header('Location: web.php?action=posts_list');
        exit;
    }
    else if ($action === 'post_like') {
        header('Content-Type: application/json');
        $user_id = $_SESSION['user']['id'] ?? 0;
        $post_id = intval($_POST['post_id'] ?? 0);
        if ($user_id <= 0 || $post_id <= 0) {
            echo json_encode(['error' => 'Մուտք գործեք լայք անելու համար։']);
            exit;
        }
        $result = likePost($con, $user_id, $post_id);
        if ($result === 'liked' || $result === 'unliked') {
            echo json_encode(['success' => $result]);
        } else {
            echo json_encode(['error' => 'Չհաջողվեց լայք/անջատել։']);
        }
        exit;
    }
    else if ($action === 'add_comment') {
        header('Content-Type: application/json');
        $user_id = $_SESSION['user']['id'] ?? 0;
        $post_id = intval($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        if ($user_id <= 0) {
            echo json_encode(['error' => 'Մուտք գործեք մեկնաբանելու համար։']);
        } else if ($post_id <= 0 || empty($content)) {
            echo json_encode(['error' => 'Դատարկ տվյալներ։']);
        } else {
            echo json_encode(
                addComment($con, $user_id, $post_id, $content)
                    ? ['success' => true]
                    : ['error' => 'Չհաջողվեց ավելացնել մեկնաբանություն։']
            );
        }
        exit;
    }
    else if ($action === 'toggle_follow') {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user'])) {
            echo json_encode(['error' => 'Մուտք գործեք՝ հետևելու համար։']);
            exit;
        }

        $follower_id = $_SESSION['user']['id'];
        $followed_id = intval($_POST['followed_id'] ?? 0);

        if ($follower_id === $followed_id || $followed_id === 0) {
            echo json_encode(['error' => 'Անթույլատրելի գործողություն։']);
            exit;
        }

        require_once __DIR__ . '/../server/posts/posts.php';

        $status = toggleFollow($con, $follower_id, $followed_id);
        $followersCount = getFollowersCount($con, $followed_id);

        echo json_encode(['status' => $status, 'followersCount' => $followersCount]);
        exit;
    }

    else {
        http_response_code(404);
        echo "Անհայտ POST գործողություն։";
        exit;
    }
}

else {

    if ($action === 'register_form') {
        require __DIR__ . '/../views/auth/register_form.php';
        exit;
    }

    else if ($action === 'login_form') {
        require __DIR__ . '/../views/auth/login_form.php';
        exit;
    }

    else if ($action === 'index') {
        require __DIR__ . '/../views/index.php';
        exit;
    }

    else if ($action === 'reels') {
        require __DIR__ . '/../views/reels.php';
        exit;
    }
    else if ($action === 'user_reels') {
        $author_id = intval($_GET['id'] ?? 0);
        $result = mysqli_query($con, "
        SELECT posts.*, users.name AS author_name, users.id AS author_id
        FROM posts
        JOIN users ON users.id = posts.user_id
        WHERE video_path != '' AND posts.user_id = $author_id
        ORDER BY created_at DESC
    ");
        $videos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $row['full_url'] = '/uploads/videos/' . $row['video_path'];
            $videos[] = $row;
        }
        $followersCount = getFollowersCount($con, $author_id);
        $followingCount = getFollowingCount($con, $author_id);
        require __DIR__ . '/../views/user_reels.php';
        exit;
    }

    else if ($action === 'post_create_form') {
        require __DIR__ . '/../views/posts/post_create_form.php';
        exit;
    }

    else if ($action === 'post_edit_form') {
        $id = intval($_GET['id'] ?? 0);
        $post = getPostById($con, $id);
        if (!$post) {
            header('Location: web.php?action=posts_list');
            exit;
        }
        require __DIR__ . '/../views/posts/post_edit_form.php';
        exit;
    }

    else if ($action === 'posts_list') {
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) {
            $post = getPostById($con, $id);
            if (!$post) {
                header('Location: web.php?action=posts_list');
                exit;
            }
            require __DIR__ . '/../views/posts/post_view.php';
        } else {
            require __DIR__ . '/../views/posts/posts_list.php';
        }
        exit;
    }

    else if ($action === 'post_view') {
        require __DIR__ . '/../views/posts/post_view.php';
        exit;
    }

    else if ($action === 'logout') {
        logout();
        header('Location: web.php?action=login_form');
        exit;
    }

    else if ($action === '') {
        require __DIR__ . '/../views/index.php';
        exit;
    }

    else {
        if (!isset($_SESSION['user'])) {
            if ($action !== 'login_form') {
                header('Location: web.php?action=login_form');
                exit;
            }
        } else {
            echo "⚠️ Սխալ կամ չճանաչված գործողություն։<br>";
            echo "Action: " . htmlspecialchars($action) . "<br>";
            echo "Ընդունելի գործողություններն են՝ register_form, login_form, index, reels, post_create_form, և այլն։";
            exit;
        }
    }

}
