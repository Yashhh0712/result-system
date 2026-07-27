<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $uri;

if ($uri !== '/' && is_file($file)) {
    return false;
}

if ($uri === '/' || $uri === '') {
    require __DIR__ . '/login.php';
} elseif (is_file($file)) {
    require $file;
} else {
    http_response_code(404);
    echo '404 Not Found';
}
