<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);function getAllPosts($con) {
    $result = mysqli_query($con, "SELECT * FROM posts ORDER BY created_at DESC");
    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = $row;
    }
    return $posts;
}

function getPostById($con, int $id) {
    $id = intval($id);
    if ($id <= 0) {
        return null;
    }
    $sql = "SELECT * FROM posts WHERE id=$id";
    $result = mysqli_query($con, $sql);
    return mysqli_fetch_assoc($result);
}

function createPost($con, string $title, string $content, array $file) {
    $errors = [];
    $title = trim($title);
    $content = trim($content);

    if ($title === '') $errors[] = 'Վերնագիրը պարտադիր է։';
    if ($content === '') $errors[] = 'Բովանդակությունը պարտադիր է։';
    if (!isset($_SESSION['user']['id'])) $errors[] = 'Օգտատերը մուտք գործած չէ։';

    $video_path = null;

    if (!empty($file) && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Վիդեոյի վերբեռնումը ձախողվեց։';
        } else {
            $allowed_types = ['video/mp4', 'video/webm', 'video/ogg'];
            $mime_type = mime_content_type($file['tmp_name']);

            if (!in_array($mime_type, $allowed_types)) {
                $errors[] = 'Թույլատրվում են միայն MP4, WebM կամ OGG ֆորմատները։';
            } else {
                $upload_dir = '../public_html/uploads/videos/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('video_') . '.' . $ext;
                $target_path = $upload_dir . $filename;

                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    $video_path = 'public_html/uploads/videos/' . $filename;
                } else {
                    $errors[] = 'Չհաջողվեց պահպանել վիդեոն։';
                }
            }
        }
    }

    if (!empty($errors)) {
        return ['status' => false, 'errors' => $errors];
    }

    $title_safe = mysqli_real_escape_string($con, $title);
    $content_safe = mysqli_real_escape_string($con, $content);
    $video_safe = $video_path ? "'" . mysqli_real_escape_string($con, $video_path) . "'" : "NULL";
    $user_id = intval($_SESSION['user']['id']);

    $sql = "INSERT INTO posts (user_id, title, content, video_path, created_at, updated_at) 
            VALUES ($user_id, '$title_safe', '$content_safe', $video_safe, NOW(), NOW())";

    if (mysqli_query($con, $sql)) {
        return ['status' => true];
    } else {
        return ['status' => false, 'errors' => ['Չհաջողվեց պահպանել գրառումը՝ ' . mysqli_error($con)]];
    }
}

function updatePost(mysqli $con, int $id, string $title, string $content, $video = null){
    $errors = [];

    if (trim($title) === '' || trim($content) === '') {
        $errors[] = 'Վերնագիրը և բովանդակությունը պարտադիր են։';
    }

    if (!empty($errors)) {
        return ['status' => false, 'errors' => $errors];
    }

    $video_path = null;
    if ($video && $video['error'] === 0) {
        $ext = pathinfo($video['name'], PATHINFO_EXTENSION);
        $new_name = 'video_' . uniqid() . '.' . $ext;
        $target_path = __DIR__ . '/../../uploads/videos/' . $new_name;

        if (move_uploaded_file($video['tmp_name'], $target_path)) {
            $video_path = 'uploads/videos/' . $new_name;
        } else {
            $errors[] = 'Չհաջողվեց վիդեոն պահել։';
        }
    }

    if (!empty($errors)) {
        return ['status' => false, 'errors' => $errors];
    }

    if ($video_path) {
        $stmt = $con->prepare("UPDATE posts SET title=?, content=?, video_path=? WHERE id=?");
        $stmt->bind_param("sssi", $title, $content, $video_path, $id);
    } else {
        $stmt = $con->prepare("UPDATE posts SET title=?, content=? WHERE id=?");
        $stmt->bind_param("ssi", $title, $content, $id);
    }

    $stmt->execute();
    return ['status' => true];
}

function deletePost($con, int $id) {
    $id = intval($id);
    $user_id = intval($_SESSION['user']['id'] ?? 0);
    if ($id <= 0 || $user_id <= 0) {
        return false;
    }
    $post = getPostById($con, $id);
    if (!$post || $post['user_id'] != $user_id) {
        return false;
    }

    $sql = "DELETE FROM posts WHERE id=$id AND user_id=$user_id";
    return mysqli_query($con, $sql);
}

function likePost($con, $user_id, $post_id) {
    $stmt = $con->prepare("SELECT 1 FROM post_likes WHERE user_id = ? AND post_id = ?");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $con->prepare("DELETE FROM post_likes WHERE user_id = ? AND post_id = ?");
        $stmt->bind_param("ii", $user_id, $post_id);
        $stmt->execute();
        return 'unliked';
    } else {
        $stmt = $con->prepare("INSERT INTO post_likes (user_id, post_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $post_id);
        $stmt->execute();
        return 'liked';
    }
}

function getPostLikesCount($con, int $post_id) {
    $stmt = $con->prepare("SELECT COUNT(*) AS count FROM post_likes WHERE post_id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'] ?? 0;
}
function hasUserLikedPost($con, int $user_id, int $post_id) {
    $stmt = $con->prepare("SELECT 1 FROM post_likes WHERE user_id = ? AND post_id = ?");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}
function addComment($con, $user_id, $post_id, $content) {
    $stmt = $con->prepare("INSERT INTO comments (user_id, post_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $post_id, $content);
    return $stmt->execute();
}
function getPostComments($con, $post_id) {
    $stmt = $con->prepare("SELECT comments.*, users.name FROM comments 
                           JOIN users ON users.id = comments.user_id
                           WHERE post_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
function isFollowing($con, int $follower_id, int $followed_id): bool {
    $stmt = $con->prepare("SELECT 1 FROM follows WHERE follower_id = ? AND followed_id = ?");
    $stmt->bind_param("ii", $follower_id, $followed_id);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}
function getFollowersCount($con, $user_id) {
    $stmt = $con->prepare("SELECT COUNT(*) AS count FROM follows WHERE followed_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'] ?? 0;
}

function getFollowingCount($con, $user_id) {
    $stmt = $con->prepare("SELECT COUNT(*) AS count FROM follows WHERE follower_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'] ?? 0;
}
function followUser($con, int $user_id, int $target_id): bool {
    if ($user_id === $target_id || isFollowing($con, $user_id, $target_id)) return false;

    $stmt = $con->prepare("INSERT INTO follows (follower_id, followed_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $target_id);
    return $stmt->execute();
}

function unfollowUser($con, int $user_id, int $target_id): bool {
    $stmt = $con->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?");
    $stmt->bind_param("ii", $user_id, $target_id);
    return $stmt->execute();
}

function toggleFollow($con, $follower_id, $followed_id)
{
    if (isFollowing($con, $follower_id, $followed_id)) {
        $stmt = $con->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?");
        $stmt->bind_param("ii", $follower_id, $followed_id);
        $stmt->execute();
        return 'unfollowed';
    } else {
        $stmt = $con->prepare("INSERT INTO follows (follower_id, followed_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $follower_id, $followed_id);
        $stmt->execute();
        return 'followed';
    }
}