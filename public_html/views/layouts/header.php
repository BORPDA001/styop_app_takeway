<?php
include __DIR__ . '/../../routes/helper.php';
$logged_in = isset($_SESSION['user']) && !empty($_SESSION['user']);
$page_title = "home";
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: 600;
            font-size: 1.5rem;
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
        }
        footer {
            font-size: 0.875rem;
        }
        .disabled-link {
            pointer-events: none;
            cursor: not-allowed;
            opacity: 0.7;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="web.php?action=index">
            💻 <?= $logged_in ? htmlspecialchars($_SESSION['user']['name']) . ' Panel' : 'Styop Project' ?>
        </a>

        <?php if ($logged_in): ?>
            <ul class="navbar-nav ms-auto me-3">
                <li class="nav-item">
                    <a href="web.php?action=reels" class="nav-link">
                        <i class="bi bi-camera-reels"></i> Reels
                    </a>
                </li>
            </ul>

            <div class="dropdown">
                <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?= htmlspecialchars($_SESSION['user']['name']) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item text-danger" href="web.php?action=logout">Դուրս գալ</a></li>
                </ul>
            </div>
        <?php else: ?>
            <div class="ms-auto">
                <a href="web.php?action=login_form" class="btn btn-outline-light btn-sm">Մուտք</a>
                <a href="web.php?action=register_form" class="btn btn-outline-light btn-sm ms-2">Գրանցվել</a>
            </div>
        <?php endif; ?>
    </div>
</nav>
