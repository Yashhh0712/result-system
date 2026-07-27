<?php
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'result_system';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$port = getenv('DB_PORT') ?: '3306';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    $isExternal = !in_array($host, ['localhost', '127.0.0.1', '::1', '']);
    if ($isExternal) {
        $caPath = '/etc/ssl/certs/tidb-ca.pem';
        $options[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    $pdo = new PDO($dsn, $username, $password, $options);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function getStudentId() {
    return $_SESSION['student_id'] ?? null;
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags($data));
}
?>
