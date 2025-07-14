<?php

if (!function_exists('redirect')) {
function redirect($dir, array $data = null)
{
    $scheme = isset($_SERVER['REQUEST_SCHEME'])
        ? $_SERVER['REQUEST_SCHEME']
        : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http');
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = $scheme . '://' . $host . '/public_html';

    if ($data !== null) {
        header("Location: $baseUrl$dir.php?" . $data["key"] . "=" . json_encode($data["value"], true));
    } else {
        header("Location: $baseUrl$dir.php");
    }
}
}
?>
