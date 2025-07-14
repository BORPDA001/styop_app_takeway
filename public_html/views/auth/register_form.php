<?php
$errors = $_SESSION['register_errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['register_errors'], $_SESSION['old']);
?>
<!DOCTYPE html>
<html lang="hy">
<head>
    <meta charset="UTF-8">
    <title>Գրանցում</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
            background: #f2f2f2;
            font-family: Arial, sans-serif;
        }

        .container {
            width: 360px;
            margin: 80px auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

        button {
            width: 100%;
            background: #4CAF50;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #45a049;
        }

        .error {
            background: #ffe0e0;
            color: #b30000;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .link {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .link a {
            color: #4CAF50;
            text-decoration: none;
        }

        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Գրանցում</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e): ?>
                <p><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="/public_html/routes/web.php?action=register" method="post">
        <input type="hidden" name="action" value="register">
        <input type="text" name="name" placeholder="Անուն" value="<?= htmlspecialchars($old['name'] ?? '') ?>">
        <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
        <input type="password" name="password" placeholder="Գաղտնաբառ">
        <button type="submit">Գրանցվել</button>
    </form>

    <div class="link">
        <p>Արդեն ունե՞ս հաշիվ? <a href="../routes/web.php?action=login_form">Մուտք գործել</a></p>
    </div>
    <a href="../index.php" class="btn btn-secondary mb-4" style="margin-left: 90px">Գլխավոր</a>
</div>
</body>
</html>
